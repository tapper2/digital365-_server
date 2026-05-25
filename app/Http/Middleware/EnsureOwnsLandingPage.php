<?php

namespace App\Http\Middleware;

use App\Models\LandingPage;
use Closure;
use Illuminate\Http\Request;

class EnsureOwnsLandingPage
{
    public function handle(Request $request, Closure $next)
    {
        $landingPage = $request->route('landingPage');

        if ($landingPage && $landingPage->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return $next($request);
    }
}
