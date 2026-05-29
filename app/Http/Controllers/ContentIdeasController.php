<?php

namespace App\Http\Controllers;

use App\Services\AI\AIProviderInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentIdeasController extends Controller
{
    public function __construct(private AIProviderInterface $ai) {}

    public function generate(Request $request): JsonResponse
    {
        set_time_limit(60);

        $validated = $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'input_text' => ['nullable', 'string'],
            'input_url'  => ['nullable', 'url', 'max:500'],
        ]);

        $ideas = $this->ai->generateContentIdeas($validated);

        return response()->json($ideas);
    }
}
