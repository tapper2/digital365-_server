<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use App\Models\LandingPageStyle;
use App\Models\LandingPageVariant;
use App\Services\AI\AIProviderInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LandingPageEditorController extends Controller
{
    public function __construct(private AIProviderInterface $ai) {}

    public function generateVariants(LandingPage $landingPage): JsonResponse
    {
        set_time_limit(0); // disable PHP timeout — image generation takes ~30s per image

        $landingPage->load('assets');

        $logoAsset      = $landingPage->assets->firstWhere('type', 'logo');
        $imageAssets    = $landingPage->assets->where('type', 'image');
        $referenceAsset = $landingPage->assets->firstWhere('type', 'reference');

        // Resolve selected style prompts from DB
        $styleIds     = array_map('intval', $landingPage->settings['selected_style_ids'] ?? []);
        $stylesById   = LandingPageStyle::whereIn('id', $styleIds)->get()->keyBy('id');
        $stylePrompts = collect($styleIds)
            ->filter(fn($id) => $stylesById->has($id))
            ->map(fn($id) => $stylesById[$id]->image_prompt)
            ->values()
            ->toArray();

        $variantContents = $landingPage->settings['variant_contents'] ?? [];

        $input = [
            'landing_page_id' => $landingPage->id,
            'title'           => $landingPage->title,
            'text'            => $landingPage->input_text ?? '',
            'url'             => $landingPage->input_url ?? '',
            'settings'        => $landingPage->settings ?? [],
            'logo_path'       => $logoAsset?->path,
            'image_paths'     => $imageAssets->pluck('path')->values()->toArray(),
            'reference_path'  => $referenceAsset?->path,
            'style_prompts'   => $stylePrompts,
            'variant_contents' => $variantContents,
        ];

        $result    = $this->ai->generateMarketingImages($input);
        $imageUrls = $result['image_urls'] ?? [];
        $content   = $result['content']   ?? [];

        // Save AI-generated marketing copy to settings so template can use it
        if (!empty($content)) {
            $settings            = $landingPage->settings ?? [];
            $settings['content'] = $content;
            $landingPage->update(['settings' => $settings]);
        }

        // Delete old variants
        $landingPage->variants()->delete();

        $created = [];
        foreach ($imageUrls as $index => $imageUrl) {
            $variant = LandingPageVariant::create([
                'landing_page_id' => $landingPage->id,
                'variant_index'   => $index + 1,
                'html_content'    => null,
                'image_url'       => $imageUrl,
            ]);
            $created[] = [
                'id'            => $variant->id,
                'variant_index' => $variant->variant_index,
                'image_url'     => $variant->image_url,
            ];
        }

        $landingPage->update(['status' => 'draft', 'selected_variant_id' => null]);

        return response()->json(['variants' => $created]);
    }

    public function selectVariant(Request $request, LandingPage $landingPage): JsonResponse
    {
        $validated = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:landing_page_variants,id'],
        ]);

        $variant = LandingPageVariant::where('id', $validated['variant_id'])
            ->where('landing_page_id', $landingPage->id)
            ->firstOrFail();

        $landingPage->update(['selected_variant_id' => $variant->id]);

        return response()->json(['message' => 'Variant selected.', 'variant' => $variant]);
    }

    public function aiEdit(Request $request, LandingPage $landingPage): JsonResponse
    {
        $validated = $request->validate([
            'instruction' => ['required', 'string', 'max:1000'],
        ]);

        $variant = $landingPage->selectedVariant;

        if (! $variant) {
            return response()->json(['message' => 'No variant selected.'], 422);
        }

        // If the variant has HTML content (legacy), edit that
        if ($variant->html_content) {
            $updatedHtml = $this->ai->applyEdit($variant->html_content, $validated['instruction']);
            $variant->update(['html_content' => $updatedHtml]);
            return response()->json(['html_content' => $updatedHtml, 'variant_id' => $variant->id]);
        }

        return response()->json(['message' => 'AI editing is available for HTML variants only.'], 422);
    }

    public function publish(LandingPage $landingPage): JsonResponse
    {
        if (! $landingPage->selected_variant_id) {
            return response()->json(['message' => 'Select a variant first.'], 422);
        }

        $landingPage->update(['status' => 'published']);

        $publicUrl = config('app.url') . '/p/' . $landingPage->token;

        return response()->json([
            'message'    => 'Published successfully.',
            'public_url' => $publicUrl,
            'token'      => $landingPage->token,
        ]);
    }
}
