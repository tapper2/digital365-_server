<?php

namespace App\Http\Controllers;

use App\Models\LandingPageStyle;
use Illuminate\Http\JsonResponse;

class LandingPageStyleController extends Controller
{
    public function index(): JsonResponse
    {
        $styles = LandingPageStyle::active()
            ->get(['id', 'name_he', 'name_en', 'tags_he', 'suitable_for_he', 'sort_order']);

        return response()->json($styles);
    }
}
