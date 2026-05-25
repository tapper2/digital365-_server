<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class OpenAIService implements AIProviderInterface
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
        $this->model  = config('services.openai.model', 'gpt-4o');
    }

    /**
     * Step 1: gpt-4o generates Hebrew marketing copy.
     * Step 2: gpt-image-1 generates a fully-designed marketing banner image
     *         with the Hebrew text already rendered inside the image.
     *
     * @return array{ image_urls: array<string|null>, content: array }
     */
    public function generateMarketingImages(array $input): array
    {
        // Step 1: Generate marketing content in Hebrew
        $content = $this->generateMarketingContent($input);

        // Step 2: Generate one variant per selected style (fallback to 2 defaults)
        $stylePrompts = $input['style_prompts'] ?? [
            'Dark navy blue corporate photography. Modern glass skyscrapers at blue-hour twilight. Person on right. Left panel: deep navy background.',
            'Black and gold classic luxury photography. Dark mahogany library setting. Person on right. Left panel: deep black with gold accent.',
        ];

        $personPhotos = $input['image_paths'] ?? [];
        $imageUrls    = [];

        foreach ($stylePrompts as $i => $stylePrompt) {
            $photoPath = !empty($personPhotos)
                ? $personPhotos[$i % count($personPhotos)]
                : null;

            try {
                $b64  = $this->generateMarketingBanner($photoPath, $stylePrompt, $content);
                $path = "landing-pages/{$input['landing_page_id']}/variants/variant_" . ($i + 1) . '.png';
                Storage::disk('public')->put($path, base64_decode($b64));
                $imageUrls[] = url(Storage::disk('public')->url($path));
            } catch (\Throwable $e) {
                Log::error("Variant " . ($i + 1) . " failed", ['error' => $e->getMessage()]);
                $imageUrls[] = null;
            }
        }

        return [
            'image_urls' => $imageUrls,
            'content'    => $content,
        ];
    }

    public function applyEdit(string $currentHtml, string $instruction): string
    {
        $system = 'אתה מומחה HTML/CSS. החזר HTML מעודכן בלבד — ללא הסברים, ללא markdown.';
        $user   = "HTML נוכחי:\n{$currentHtml}\n\nהוראה: {$instruction}\n\nהחזר HTML מעודכן בלבד:";
        return $this->extractHtml($this->chat($system, $user, 8192));
    }

    // ─── Step 1: Scrape website + generate Hebrew marketing copy ───────────────

    private function generateMarketingContent(array $input): array
    {
        // Pull real content from the website so gpt-4o understands the actual business
        $scrapedContent = '';
        if (!empty($input['url'])) {
            $scrapedContent = $this->scrapeWebsite($input['url']);
        }

        $websiteBlock = $scrapedContent
            ? "תוכן האתר (נשלף אוטומטית):\n{$scrapedContent}\n\n"
            : '';

        $system = <<<SYS
אתה קופירייטר שיווקי מקצועי בעברית.
חובה: זהה את סוג העסק בדיוק לפי התוכן שמסופק — עורך דין, רואה חשבון, מתווך, מהנדס וכו׳.
אל תערבב מקצועות. אל תנחש. בסס הכל על הנתונים בלבד.
החזר JSON תקין בלבד, ללא markdown.
SYS;

        $user = <<<PROMPT
שם העסק: {$input['title']}
תיאור שסיפק הלקוח: {$input['text']}

{$websiteBlock}הוראות:
1. קרא את תוכן האתר בעיון וזהה את התחום המדויק
2. השתמש במינוח המקצועי הנכון בלבד
3. אל תזכיר מקצועות שאינם מופיעים בתוכן

החזר JSON בלבד:
{
  "headline_line1": "שורה ראשונה — 3-4 מילים, שם/תחום המקצוע",
  "headline_line2": "שורה שניה — 3-5 מילים, יתרון עיקרי, בצבע זהב",
  "headline_line3": "שורה שלישית — 2-4 מילים, תוצאה או קהל יעד",
  "trust_badge": "משפט אמון קצר ומדויק לתחום הספציפי",
  "subtext": "1-2 משפטים על העסק, בשפת התחום",
  "services": [
    {"icon": "🏛️", "label": "שירות ספציפי 1"},
    {"icon": "⚖️", "label": "שירות ספציפי 2"},
    {"icon": "📋", "label": "שירות ספציפי 3"},
    {"icon": "🏠", "label": "שירות ספציפי 4"}
  ]
}
PROMPT;

        $raw = $this->chat($system, $user, 800);
        preg_match('/\{.*\}/s', $raw, $m);
        $decoded = json_decode($m[0] ?? '{}', true);
        return is_array($decoded) ? $decoded : [];
    }

    // ─── Website scraper ────────────────────────────────────────────────────────

    private function scrapeWebsite(string $url): string
    {
        try {
            $response = Http::timeout(12)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; Digital365/1.0; +https://digital365.co.il)'])
                ->get($url);

            if ($response->failed()) return '';

            $html = $response->body();

            libxml_use_internal_errors(true);
            $doc = new \DOMDocument();
            $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            libxml_clear_errors();

            $xpath  = new \DOMXPath($doc);
            $chunks = [];

            // Remove script/style nodes so we don't get JS noise
            foreach ($xpath->query('//script | //style | //nav | //footer') as $node) {
                $node->parentNode?->removeChild($node);
            }

            // Title
            $nodes = $xpath->query('//title');
            if ($nodes->length) $chunks[] = 'title: ' . $this->cleanText($nodes->item(0)->textContent);

            // Meta description
            $nodes = $xpath->query('//meta[@name="description"]/@content');
            if ($nodes->length) $chunks[] = 'description: ' . $this->cleanText($nodes->item(0)->textContent);

            // H1 + H2
            foreach ($xpath->query('//h1 | //h2') as $node) {
                $t = $this->cleanText($node->textContent);
                if (mb_strlen($t) > 2) $chunks[] = "{$node->nodeName}: {$t}";
            }

            // Paragraphs (first 12 meaningful ones)
            $pCount = 0;
            foreach ($xpath->query('//p') as $node) {
                $t = $this->cleanText($node->textContent);
                if (mb_strlen($t) > 40) {
                    $chunks[] = $t;
                    if (++$pCount >= 12) break;
                }
            }

            // Truncate total to ~2500 chars so the LLM prompt stays manageable
            $result = implode("\n", $chunks);
            return mb_substr($result, 0, 2500);

        } catch (\Throwable $e) {
            Log::warning('Website scrape failed', ['url' => $url, 'error' => $e->getMessage()]);
            return '';
        }
    }

    private function cleanText(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    // ─── Step 2: Full marketing banner with designed text inside the image ──────

    private function generateMarketingBanner(?string $photoPath, string $stylePrompt, array $content): string
    {
        $headline1  = $content['headline_line1'] ?? '';
        $headline2  = $content['headline_line2'] ?? '';
        $headline3  = $content['headline_line3'] ?? '';
        $trustBadge = $content['trust_badge']    ?? '';

        $serviceLabels = '';
        if (!empty($content['services'])) {
            $labels = array_map(fn($s) => '"' . ($s['label'] ?? '') . '"', $content['services']);
            $serviceLabels = implode("\n", $labels);
        }

        $prompt = <<<PROMPT
Create a premium Israeli professional advertisement banner.

Use the uploaded person as the exact reference.
Preserve their facial identity, hair, age, skin tone, body shape and clothing exactly.
Do not beautify, redraw, or reinterpret their face or body in any way.

Visual style: {$stylePrompt}

Right side: the person, professional portrait, mid-thigh crop, facing slightly left toward camera.
Left side: designed Hebrew advertising layout matching the visual style.

Hebrew text must be rendered exactly as written — clean, legible, right-aligned:
"{$headline1}"
"{$headline2}"
"{$headline3}"

Small trust badge with thin gold border at the top:
"{$trustBadge}"

Bottom service labels in a horizontal row:
{$serviceLabels}

BOTTOM STRIP — MANDATORY:
The bottom 52 pixels of the image must be a solid flat black (#000000) bar — no texture, no gradient, no image content bleeding into it. This strip is reserved for HTML overlay content placed on top after generation.

No extra text.
No fake letters.
No distorted Hebrew.
No watermarks.
No logos.
Wide landscape 1536x1024, photorealistic high-quality commercial photography.
PROMPT;

        $http     = Http::withToken($this->apiKey)->timeout(180);
        $hasPhoto = false;

        if ($photoPath) {
            $fileContent = Storage::disk('public')->get($photoPath);
            if ($fileContent) {
                $ext      = pathinfo($photoPath, PATHINFO_EXTENSION) ?: 'jpg';
                $http     = $http->attach('image[]', $fileContent, "person.{$ext}");
                $hasPhoto = true;
            }
        }

        $endpoint = $hasPhoto ? '/images/edits' : '/images/generations';
        $response = ($hasPhoto ? $http : Http::withToken($this->apiKey)->timeout(180))
            ->post("{$this->baseUrl}{$endpoint}", [
                'model'   => 'gpt-image-2',
                'prompt'  => $prompt,
                'size'    => '1536x1024',
                'quality' => 'medium',
                'n'       => $hasPhoto ? '1' : 1,
            ]);

        if ($response->failed()) {
            Log::error('gpt-image-2 error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException('Image generation failed: ' . $response->status());
        }

        $b64 = $response->json('data.0.b64_json');
        if (!$b64) {
            throw new RuntimeException('No image data returned');
        }

        return $b64;
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function chat(string $system, string $user, int $maxTokens = 4096): string
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post("{$this->baseUrl}/chat/completions", [
                'model'      => $this->model,
                'max_tokens' => $maxTokens,
                'messages'   => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user',   'content' => $user],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Chat API failed: ' . $response->status());
        }

        return $response->json('choices.0.message.content', '');
    }

    private function extractHtml(string $content): string
    {
        $content = preg_replace('/^```html?\s*/i', '', trim($content));
        $content = preg_replace('/```\s*$/', '', $content);
        return trim($content);
    }
}
