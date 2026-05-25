<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use App\Models\LandingPageAsset;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function __construct(private FileUploadService $uploader) {}

    public function index(Request $request): JsonResponse
    {
        $pages = $request->user()
            ->landingPages()
            ->with(['selectedVariant', 'assets'])
            ->withCount('leads')
            ->latest()
            ->get();

        return response()->json($pages);
    }

    public function store(Request $request): JsonResponse
    {
        if ($request->has('settings') && is_string($request->input('settings'))) {
            $request->merge(['settings' => json_decode($request->input('settings'), true)]);
        }

        $validated = $request->validate([
            'title'                 => ['required', 'string', 'max:255'],
            'input_text'            => ['nullable', 'string'],
            'input_url'             => ['nullable', 'url', 'max:500'],
            'form_fields'           => ['nullable', 'array'],
            'form_fields.*'         => ['string'],
            'settings'              => ['nullable', 'array'],
            'logo'                  => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,svg,webp', 'max:5120'],
            'images'                => ['nullable', 'array', 'max:5'],
            'images.*'              => ['file', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ]);

        $page = $request->user()->landingPages()->create([
            'title'       => $validated['title'],
            'input_text'  => $validated['input_text'] ?? null,
            'input_url'   => $validated['input_url'] ?? null,
            'form_fields' => $validated['form_fields'] ?? ['name', 'email', 'phone'],
            'settings'    => $validated['settings'] ?? [],
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $path = $this->uploader->storeAsset($request->file('logo'), $page->id, 'logo');
            LandingPageAsset::create([
                'landing_page_id' => $page->id,
                'type'            => 'logo',
                'path'            => $path,
                'original_name'   => $request->file('logo')->getClientOriginalName(),
            ]);
        }

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $this->uploader->storeAsset($image, $page->id, 'image');
                LandingPageAsset::create([
                    'landing_page_id' => $page->id,
                    'type'            => 'image',
                    'path'            => $path,
                    'original_name'   => $image->getClientOriginalName(),
                ]);
            }
        }

        return response()->json($page->load('assets'), 201);
    }

    public function show(LandingPage $landingPage): JsonResponse
    {
        return response()->json(
            $landingPage->load(['variants', 'assets', 'selectedVariant'])
        );
    }

    public function update(Request $request, LandingPage $landingPage): JsonResponse
    {
        if ($request->has('settings') && is_string($request->input('settings'))) {
            $request->merge(['settings' => json_decode($request->input('settings'), true)]);
        }

        $validated = $request->validate([
            'title'       => ['sometimes', 'string', 'max:255'],
            'form_fields' => ['sometimes', 'array'],
            'settings'    => ['sometimes', 'array'],
            'input_text'  => ['sometimes', 'nullable', 'string'],
            'input_url'   => ['sometimes', 'nullable', 'url', 'max:500'],
        ]);

        $landingPage->update($validated);

        return response()->json($landingPage->fresh(['variants', 'assets']));
    }

    public function destroy(LandingPage $landingPage): JsonResponse
    {
        foreach ($landingPage->assets as $asset) {
            app(FileUploadService::class)->deleteAsset($asset->path);
        }

        $landingPage->delete();

        return response()->json(['message' => 'Landing page deleted.']);
    }
}
