<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use App\Models\Lead;
use App\Models\LeadNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(LandingPage $landingPage): JsonResponse
    {
        $leads = $landingPage->leads()
            ->with('notes.user:id,name')
            ->latest()
            ->paginate(20);

        return response()->json($leads);
    }

    public function show(Request $request, Lead $lead): JsonResponse
    {
        // Ensure the authenticated user owns the landing page
        if ($lead->landingPage->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json($lead->load('notes.user:id,name'));
    }

    public function updateStatus(Request $request, Lead $lead): JsonResponse
    {
        if ($lead->landingPage->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:new,in_progress,handled,rejected'],
        ]);

        $lead->update($validated);

        return response()->json($lead);
    }

    public function addNote(Request $request, Lead $lead): JsonResponse
    {
        if ($lead->landingPage->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $note = LeadNote::create([
            'lead_id' => $lead->id,
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        return response()->json($note->load('user:id,name'), 201);
    }

    public function submit(Request $request, string $token): JsonResponse
    {
        $page = LandingPage::where('token', $token)
            ->where('status', 'published')
            ->firstOrFail();

        $allowedFields = $page->form_fields ?? ['name', 'email', 'phone', 'message'];

        $data = $request->only($allowedFields);

        if (empty(array_filter($data))) {
            return response()->json(['message' => 'No data provided.'], 422);
        }

        Lead::create([
            'landing_page_id' => $page->id,
            'data'            => $data,
            'ip_address'      => $request->ip(),
            'user_agent'      => $request->userAgent(),
        ]);

        return response()->json(['message' => 'Thank you! Your form was submitted successfully.'], 201);
    }
}
