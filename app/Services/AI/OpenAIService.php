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

    // ─── Public entry point ─────────────────────────────────────────────────────

    /**
     * Step 1: gpt-4o generates Hebrew marketing copy.
     * Step 2: gpt-image-2 generates a fully-designed banner per selected style.
     *
     * @return array{ image_urls: array<string|null>, content: array }
     */
    public function generateMarketingImages(array $input): array
    {
        $stylePrompts = $input['style_prompts'] ?? [
            'Dark navy blue corporate photography. Modern glass skyscrapers at blue-hour twilight. Person on right. Left panel: deep navy background.',
            'Black and gold classic luxury photography. Dark mahogany library setting. Person on right. Left panel: deep black with gold accent.',
        ];

        $personPhotos    = $input['image_paths'] ?? [];
        $variantContents = $input['variant_contents'] ?? [];
        $imageUrls       = [];
        $firstContent    = [];

        foreach ($stylePrompts as $i => $stylePrompt) {
            // Use preset content (from content picker) if available, otherwise generate fresh
            $content = !empty($variantContents[$i])
                ? $variantContents[$i]
                : $this->generateMarketingContent($input, $i + 1, count($stylePrompts));

            // Keep the first variant's content as the canonical page content
            if ($i === 0) {
                $firstContent = $content;
            }

            $photoPath = !empty($personPhotos)
                ? $personPhotos[$i % count($personPhotos)]
                : null;

            try {
                $b64  = $this->generateMarketingBanner($photoPath, $stylePrompt, $content, $i + 1, $input['reference_path'] ?? null, $input['logo_path'] ?? null);
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
            'content'    => $firstContent,
        ];
    }

    public function applyEdit(string $currentHtml, string $instruction): string
    {
        $system = 'אתה מומחה HTML/CSS. החזר HTML מעודכן בלבד — ללא הסברים, ללא markdown.';
        $user   = "HTML נוכחי:\n{$currentHtml}\n\nהוראה: {$instruction}\n\nהחזר HTML מעודכן בלבד:";
        return $this->extractHtml($this->chat($system, $user, 8192));
    }

    // ─── Content Ideas: 20+ options per field for the content picker ─────────────

    public function generateContentIdeas(array $input): array
    {
        $title     = $input['title']      ?? '';
        $inputText = $input['input_text'] ?? '';
        $url       = $input['input_url']  ?? '';

        $scraped = '';
        if ($url) {
            $scraped = $this->scrapeWebsite($url);
            if ($scraped) {
                $this->logLlm('scrape_ideas', ['url' => $url, 'content' => $scraped]);
            }
        }

        $websiteBlock = $scraped
            ? "תוכן האתר (נשלף אוטומטית):\n{$scraped}\n\n"
            : '';

        $system = <<<SYS
You are NOT a generic copywriter.

You are an elite Israeli creative director
who builds high-converting luxury campaigns
for premium businesses.

Your job is to create:
- sharp marketing hooks
- emotional headlines
- luxury branding language
- bold sales messaging
- authority positioning
- memorable one-liners
- psychologically persuasive Hebrew copy

The writing style must feel:
- premium
- modern
- confident
- emotionally powerful
- intelligent
- cinematic
- luxurious
- clean
- not cliché
- not generic
- not robotic

IMPORTANT RULES:

Do NOT generate:
- generic professional text
- cliché industry phrases
- boring professional wording
- weak slogans
- AI-sounding sentences
- childish marketing
- exaggerated fake promises

Avoid phrases like:
- פתרונות מתקדמים
- שירות אישי ומקצועי
- הדרך שלך להצלחה
- ייעוץ מקצועי
- צוות מנוסה
- ניסיון של שנים
- אנחנו כאן בשבילך
- שירות מקצועי

The copy must feel like:
- luxury real estate branding
- high-end financial campaign
- premium Tel Aviv agency work
- expensive Facebook/Instagram ads
- modern landing page hero section

Return ONLY valid JSON. No markdown, no explanations, no numbering.
SYS;

        $user = <<<PROMPT
BUSINESS CONTEXT:
{$title}
{$inputText}

{$websiteBlock}Based on the business context above, identify the target audience, core emotional pain points, and brand positioning — then generate premium Hebrew marketing copy options.

The copy must feel elite, conversion-focused, and emotionally persuasive.
Return ONLY this JSON structure with exactly the required number of options:

{
  "headline_line1": [],
  "headline_line2": [],
  "headline_line3": [],
  "trust_badge":    [],
  "subtext":        [],
  "cta":            [],
  "services":       []
}

GENERATE:

1. headline_line1 — HEADLINE OPTIONS
2–5 words. Bold, premium, memorable.
Direction examples (adapt style, not content):
כסף חכם מתחיל כאן / לפני שחותמים בודקים / עסקאות גדולות דורשות דיוק / המהלך שמגן על המיליונים / תכנון נכון משנה הכול
Generate 20 completely unique options.

2. headline_line2 — EMOTIONAL HOOKS
3–7 words. Urgency, desire, authority or fear of mistakes.
Direction examples:
טעות אחת יכולה לעלות ביוקר / לפני העסקה הכי חשובה שלך / מה שרוב האנשים מגלים מאוחר מדי / לשלם פחות להרוויח יותר / ההבדל בין עסקה לרווח
Generate 20 completely unique options.

3. headline_line3 — SUPPORTING LINES
3–6 words. Persuasive, conversion-focused, direct.
Must reinforce the headline and push toward action.
Generate 20 completely unique options.

4. trust_badge — TRUST BADGES
3–7 words. Authoritative, impressive, specific. Not generic.
Direction examples:
ניסיון מתוך רשות המיסים / ליווי עסקאות בהיקפים גבוהים / דיוק משפטי בעולם הנדל״ן / ניסיון שמייצר יתרון
Generate 20 completely unique options.

5. subtext — DESCRIPTION LINES
1 sharp sentence. Emotionally intelligent, zero fluff.
Sells the feeling, not the service.
Generate 10 completely unique options.

6. cta — CTA BUTTONS
2–4 words. Premium, action-driven.
Direction examples:
לבדיקה ראשונית / לתכנון העסקה / לשיחת אסטרטגיה / לפגישה דיסקרטית / מתחילים לחשוב חכם
Generate 20 completely unique options.

7. services — SERVICES
1–3 words each, premium and strategic.
Use a relevant emoji icon per service.
Direction examples:
עסקאות יוקרה / תכנון אסטרטגי / הגנת מס / ליווי יזמים / מבני השקעה / תכנון רווח
Format: {"icon": "emoji", "label": "label text"}
Generate 20 completely unique options.

Return ONLY Hebrew text in the values. No explanations outside the JSON.
PROMPT;

        // Log the full prompt sent to the LLM
        $this->logLlm('ideas/request', [
            'model'          => $this->model,
            'input_title'    => $title,
            'input_text'     => $inputText,
            'url'            => $url,
            'has_scraped'    => !empty($scraped),
            'system_prompt'  => $system,
            'user_prompt'    => $user,
        ]);

        $raw = $this->chat($system, $user, 4096);
        preg_match('/\{.*\}/s', $raw, $m);
        $decoded = json_decode($m[0] ?? '{}', true);

        $defaults = [
            'headline_line1' => [],
            'headline_line2' => [],
            'headline_line3' => [],
            'trust_badge'    => [],
            'subtext'        => [],
            'cta'            => [],
            'services'       => [],
        ];

        $result = array_merge($defaults, is_array($decoded) ? $decoded : []);

        // Log what came back
        $this->logLlm('ideas/response', [
            'raw_response' => $raw,
            'parsed'       => $result,
            'counts'       => array_map(fn($v) => is_array($v) ? count($v) : 0, $result),
        ]);

        return $result;
    }

    // ─── Step 1: Scrape + generate Hebrew marketing copy ────────────────────────

    private function generateMarketingContent(array $input, int $variantIndex = 1, int $totalVariants = 2): array
    {
        // Scrape only once (for variant 1) — cache in input array to avoid double HTTP calls
        if ($variantIndex === 1 && !empty($input['url'])) {
            $scrapedContent = $this->scrapeWebsite($input['url']);
            if ($scrapedContent) {
                $this->logLlm('scrape', [
                    'url'     => $input['url'],
                    'content' => $scrapedContent,
                ]);
            }
        } else {
            $scrapedContent = $input['_scraped'] ?? '';
        }

        // Store scrape result so subsequent variants can reuse it without re-fetching
        $input['_scraped'] = $scrapedContent;

        $websiteBlock = $scrapedContent
            ? "תוכן האתר (נשלף אוטומטית):\n{$scrapedContent}\n\n"
            : '';

        // Define a distinct creative angle per variant
        $angles = [
            1 => "CREATIVE ANGLE — AUTHORITY & PREMIUM EXPERTISE:\nFocus on: supreme authority, proven results, premium service, trust, leadership in the field.\nEmotion: confidence, security, exclusivity.\nHook style: bold statement of expertise and dominance.",
            2 => "CREATIVE ANGLE — URGENCY & FEAR OF MISTAKES:\nFocus on: costly errors without this expert, financial risk, what clients lose by waiting, urgent need for protection.\nEmotion: fear of loss, urgency, relief through action.\nHook style: sharp warning + solution.",
            3 => "CREATIVE ANGLE — TRANSFORMATION & RESULTS:\nFocus on: dramatic before/after, life-changing outcomes, financial freedom, concrete results clients get.\nEmotion: aspiration, excitement, possibility.\nHook style: vivid outcome painting.",
        ];
        $angleInstruction = $angles[$variantIndex] ?? $angles[1];

        $variantNote = $totalVariants > 1
            ? "This is VARIANT {$variantIndex} of {$totalVariants}.\nWrite COMPLETELY DIFFERENT copy from other variants.\nDifferent headlines, different emotional hooks, different structure.\n{$angleInstruction}\n\n"
            : '';

        $system = <<<SYS
You are an elite Israeli copywriter and branding strategist.
You write sharp, premium, emotionally persuasive Hebrew marketing copy
for luxury brands, top law firms, and premium real-estate companies.
You write like Wieden+Kennedy, Droga5, or Israel's top branding agencies.

STRICT WORD LIMITS — NO EXCEPTIONS:
- headline_line1: 2–4 words (brand/field — premium, sharp)
- headline_line2: 3–5 words (bold emotional hook — the most powerful line — gold color)
- headline_line3: 3–6 words (persuasive subheadline — conversion focused)
- trust_badge: 4–8 words (specific credibility — NOT generic platitudes)
- cta: 2–3 words (premium action — e.g. "קבע פגישה" / "דבר איתנו" / "גלה עוד")
- subtext: 1 sentence only, emotionally sharp, zero fluff
- services labels: 2–4 words each, field-specific

TONE RULES:
- Premium, confident, sophisticated — never generic
- Sell the EMOTION, not the service description
- Sharp and memorable — every word must earn its place
- Write as if this is a campaign for a ₪10M brand

HARD AVOIDS:
- "שירות מקצועי", "אנחנו כאן בשבילך", "צוות מנוסה", "ניסיון של שנים"
- Long explanations or descriptions
- Clichés and overused marketing phrases
- Anything that sounds like a business card, not a campaign

FROM THE CONTENT: identify the exact profession, target audience, core emotional pain,
and brand personality — then write copy that hits the emotional trigger precisely.

Return ONLY valid JSON, no markdown.
SYS;

        $user = <<<PROMPT
{$variantNote}שם העסק: {$input['title']}
תיאור שסיפק הלקוח: {$input['text']}

{$websiteBlock}הוראות:
1. זהה את התחום המדויק ואת קהל היעד
2. כתוב קופי שפוגע ברגש — לא בתיאור
3. אל תתאר את העסק — מכור את ההרגשה
4. כל מילה חייבת להרוויח את מקומה

החזר JSON בלבד:
{
  "headline_line1": "2-4 מילים — שם/תחום, חד ופרימיום",
  "headline_line2": "3-5 מילים — hook רגשי שאי אפשר להתעלם ממנו",
  "headline_line3": "3-6 מילים — משפט המרה, ישיר ומשכנע",
  "trust_badge": "4-8 מילים — אמינות ספציפית, לא גנרית",
  "cta": "2-3 מילים — קריאה לפעולה פרימיום",
  "subtext": "משפט אחד בלבד — רגשי, חד, ללא מילים מיותרות",
  "services": [
    {"icon": "🏛️", "label": "2-4 מילים ספציפיות לתחום"},
    {"icon": "⚖️", "label": "2-4 מילים ספציפיות לתחום"},
    {"icon": "📋", "label": "2-4 מילים ספציפיות לתחום"},
    {"icon": "🏠", "label": "2-4 מילים ספציפיות לתחום"}
  ]
}
PROMPT;

        $raw = $this->chat($system, $user, 1000);
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

            foreach ($xpath->query('//script | //style | //nav | //footer') as $node) {
                $node->parentNode?->removeChild($node);
            }

            $nodes = $xpath->query('//title');
            if ($nodes->length) $chunks[] = 'title: ' . $this->cleanText($nodes->item(0)->textContent);

            $nodes = $xpath->query('//meta[@name="description"]/@content');
            if ($nodes->length) $chunks[] = 'description: ' . $this->cleanText($nodes->item(0)->textContent);

            foreach ($xpath->query('//h1 | //h2') as $node) {
                $t = $this->cleanText($node->textContent);
                if (mb_strlen($t) > 2) $chunks[] = "{$node->nodeName}: {$t}";
            }

            $pCount = 0;
            foreach ($xpath->query('//p') as $node) {
                $t = $this->cleanText($node->textContent);
                if (mb_strlen($t) > 40) {
                    $chunks[] = $t;
                    if (++$pCount >= 12) break;
                }
            }

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

    // ─── Step 2: Full marketing banner with creative director direction ──────────

    private function generateMarketingBanner(
        ?string $photoPath,
        string  $stylePrompt,
        array   $content,
        int     $variantIndex = 1,
        ?string $referencePath = null,
        ?string $logoPath = null
    ): string {
        $headline1  = $content['headline_line1'] ?? '';
        $headline2  = $content['headline_line2'] ?? '';
        $headline3  = $content['headline_line3'] ?? '';
        $trustBadge = $content['trust_badge']    ?? '';
        $cta        = $content['cta']            ?? '';
        $subtext    = $content['subtext']        ?? '';

        $serviceLabels = '';
        if (!empty($content['services'])) {
            $labels = array_map(fn($s) => '"' . ($s['label'] ?? '') . '"', $content['services']);
            $serviceLabels = implode("\n", $labels);
        }

        // ── Load all image files upfront ──────────────────────────────────────────
        $disk = Storage::disk('public');

        $photoContent = $photoPath ? $disk->get($photoPath) : null;
        $logoContent  = $logoPath  ? $disk->get($logoPath)  : null;
        $refContent   = ($referencePath && $disk->exists($referencePath))
                        ? $disk->get($referencePath)
                        : null;

        $hasPhoto = !empty($photoContent);
        $hasLogo  = !empty($logoContent);
        $hasRef   = !empty($refContent);

        // ── Build dynamic "ATTACHED IMAGES" block ─────────────────────────────────
        $imgIdx  = 0;
        $roles   = [];

        if ($hasPhoto) {
            $imgIdx++;
            $roles[] = "Image {$imgIdx} — PORTRAIT (strict identity preservation):\n"
                . "Preserve facial identity, hair, age, skin tone, body shape and clothing exactly.\n"
                . "Do NOT change, alter, modify, age, de-age, beautify, or replace the person's face,\n"
                . "hair, body shape, skin tone, or clothing in ANY way.\n"
                . "The person must look identical to the upload.";
        }

        if ($hasLogo) {
            $imgIdx++;
            $roles[] = "Image {$imgIdx} — BRAND LOGO (integrate into design):\n"
                . "This is the client's official logo. Place it prominently at the top of the left advertising panel.\n"
                . "Scale it to approximately 30–40% of the panel width.\n"
                . "Adapt its color treatment to harmonize with the background (white version on dark backgrounds,\n"
                . "color version on light backgrounds) — but preserve its exact shape and symbol.";
        }

        if ($hasRef) {
            $imgIdx++;
            $roles[] = "Image {$imgIdx} — STYLE REFERENCE — HIGHEST VISUAL PRIORITY:\n"
                . "Study this reference image with extreme care before generating.\n"
                . "This shows the EXACT visual style the client wants to replicate.\n"
                . "You MUST extract and apply:\n"
                . "  • Its dominant color palette — background tones, accent colors, text colors\n"
                . "  • Its typography personality — weight (bold/light), size hierarchy, font character\n"
                . "  • Its spacing rhythm — dense or airy, padding feel, layout density\n"
                . "  • Its background treatment — solid, gradient, texture, photo blend, overlay\n"
                . "  • Its lighting mood and atmosphere\n"
                . "  • Its overall aesthetic DNA: modern/classic/bold/minimal/dark/bright\n"
                . "The final result MUST feel like it belongs to the same design family as this reference.\n"
                . "Do NOT copy any text or logos from it — only its visual language.";
        }

        $imageRoleBlock = !empty($roles)
            ? implode("\n\n", $roles)
            : "No reference images provided — create from style direction only.";

        $logoInstruction = $hasLogo
            ? "\nLOGO PLACEMENT: The brand logo (attached) must appear at the top of the left panel,\nclearly visible, sized for premium readability, adapted to match the background."
            : '';

        // ── Build prompt ──────────────────────────────────────────────────────────
        $prompt = <<<PROMPT
You are an award-winning creative director
specializing in luxury Meta advertising campaigns
and premium landing-page hero sections.

Your task is NOT to summarize a website.

Your task is to create a visually stunning,
emotionally persuasive,
high-converting premium advertising campaign visual.

The design must feel:
- modern
- luxurious
- cinematic
- editorial
- sophisticated
- highly art-directed
- conversion-oriented

This should look like:
- a premium SaaS landing page
- a luxury legal campaign
- a high-end Meta advertisement
- a professionally designed branding campaign

Visual inspiration:
Apple, Stripe, Linear, Wealthsimple,
luxury legal-tech brands,
premium Israeli real-estate firms.

The composition must include:
- strong visual hierarchy
- dominant hero typography
- emotional focal points
- premium spacing rhythm
- layered depth
- subtle gradients
- elegant lighting
- luxury UI cards
- premium icon sections
- cinematic composition
- refined CTA hierarchy

The design must communicate:
- trust
- authority
- financial intelligence
- confidence
- strategic expertise
- premium professionalism

Avoid:
- generic layouts
- website-like sections
- basic templates
- plain typography
- empty spacing
- stock-photo aesthetics

---

ATTACHED IMAGES — ROLES:
{$imageRoleBlock}

---

Create a premium wide landscape advertising visual. 1536x1024.

Layout:
- Right 55%: the person, professional portrait, mid-thigh crop, facing slightly left.
- Left 45%: premium designed Hebrew advertising layout.{$logoInstruction}

Style direction (apply on top of reference style if reference is provided):
{$stylePrompt}

---

Typography:
Use elegant premium Hebrew typography with strong emotional hierarchy.
All text must be clean, legible, CENTER-ALIGNED.
CRITICAL: Every text element on the left advertising panel MUST be horizontally centered — headlines, subheadline, trust badge, CTA button, service labels. No left-aligned or right-aligned text anywhere.

Main headline (large, dominant):
"{$headline1}"

Creative supporting hook (large, gold accent, bold marketing line):
"{$headline2}"

Subheadline (medium, white, persuasive):
"{$headline3}"

Trust indicator (small premium pill/badge with gold border, top of left panel):
"{$trustBadge}"

CTA button (premium styled button, bottom of text block):
"{$cta}"

Business description (visual context — do NOT render as text):
{$subtext}

---

Service cards / Icon row (4 items, bottom of left panel):
{$serviceLabels}

Include in the design:
- premium CTA button area
- luxury service cards with the 4 services above
- subtle icon row
- refined premium layout with layered depth

---

HARD CONSTRAINTS:
Render ONLY the Hebrew text specified above — nothing else.
No invented text. No fake letters. No distorted Hebrew glyphs.
No watermarks.
Photorealistic high-quality commercial photography.
PROMPT;

        // ── Attach images and send request ────────────────────────────────────────
        $http = Http::withToken($this->apiKey)->timeout(180);

        if ($hasPhoto) {
            $ext  = pathinfo($photoPath, PATHINFO_EXTENSION) ?: 'jpg';
            $http = $http->attach('image[]', $photoContent, "person.{$ext}");
        }
        if ($hasLogo) {
            $ext  = pathinfo($logoPath, PATHINFO_EXTENSION) ?: 'png';
            $http = $http->attach('image[]', $logoContent, "logo.{$ext}");
        }
        if ($hasRef) {
            $ext  = pathinfo($referencePath, PATHINFO_EXTENSION) ?: 'jpg';
            $http = $http->attach('image[]', $refContent, "reference.{$ext}");
        }

        $useEdits = $hasPhoto || $hasLogo || $hasRef;

        $this->logLlm("image_variant_{$variantIndex}", [
            'model'          => 'gpt-image-2',
            'endpoint'       => $useEdits ? '/images/edits' : '/images/generations',
            'has_photo'      => $hasPhoto,
            'has_logo'       => $hasLogo,
            'has_reference'  => $hasRef,
            'size'           => '1536x1024',
            'quality'        => 'medium',
            'style_prompt'   => $stylePrompt,
            'full_prompt'    => $prompt,
            'content_used'   => $content,
        ]);
        $endpoint = $useEdits ? '/images/edits' : '/images/generations';
        $response = ($useEdits ? $http : Http::withToken($this->apiKey)->timeout(180))
            ->post("{$this->baseUrl}{$endpoint}", [
                'model'   => 'gpt-image-2',
                'prompt'  => $prompt,
                'size'    => '1536x1024',
                'quality' => 'medium',
                'n'       => $useEdits ? '1' : 1,
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

        $result = $response->json('choices.0.message.content', '');

        $this->logLlm('chat_content', [
            'model'         => $this->model,
            'max_tokens'    => $maxTokens,
            'system_prompt' => $system,
            'user_prompt'   => $user,
            'response'      => $result,
            'usage'         => $response->json('usage'),
        ]);

        return $result;
    }

    private function extractHtml(string $content): string
    {
        $content = preg_replace('/^```html?\s*/i', '', trim($content));
        $content = preg_replace('/```\s*$/', '', $content);
        return trim($content);
    }

    // ─── LLM call logger ────────────────────────────────────────────────────────

    /**
     * Write a JSON log entry to storage/logs/llm/{subdir}/{type}_{ts}_{ms}.json
     * The type may contain a single "/" to specify a subdirectory, e.g. "ideas/request".
     */
    private function logLlm(string $type, array $data): void
    {
        try {
            // Support subdirectory via "subdir/name" notation
            $parts   = explode('/', $type, 2);
            $subDir  = count($parts) > 1 ? '/' . $parts[0] : '';
            $logType = $parts[count($parts) - 1];

            $dir = storage_path('logs/llm' . $subDir);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $ts       = now()->format('Y-m-d_H-i-s');
            $ms       = now()->format('v');
            $filename = "{$dir}/{$logType}_{$ts}_{$ms}.json";

            file_put_contents(
                $filename,
                json_encode(
                    array_merge(['timestamp' => now()->toIso8601String(), 'type' => $type], $data),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                )
            );
        } catch (\Throwable $e) {
            Log::warning('LLM log write failed', ['error' => $e->getMessage()]);
        }
    }
}
