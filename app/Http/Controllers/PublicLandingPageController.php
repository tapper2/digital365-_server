<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use Illuminate\Support\Facades\Storage;

class PublicLandingPageController extends Controller
{
    public function show(string $token)
    {
        $page = LandingPage::where('token', $token)
            ->where('status', 'published')
            ->with(['selectedVariant', 'assets'])
            ->firstOrFail();

        $settings     = $page->settings ?? [];
        $accent       = $settings['accent_color'] ?? '#B8952A';
        $bg           = $settings['bg_color']     ?? '#0d1117';
        $heroImageUrl = $page->selectedVariant?->image_url;
        $logoAsset    = $page->assets->firstWhere('type', 'logo');
        $logoUrl      = $logoAsset?->url;

        return view('landing-page', compact('page', 'settings', 'accent', 'bg', 'heroImageUrl', 'logoUrl'));
    }
}
