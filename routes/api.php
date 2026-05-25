<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\LandingPageEditorController;
use App\Http\Controllers\LandingPageStyleController;
use App\Http\Controllers\LeadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes (public)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('register', RegisterController::class);
    Route::post('login', LoginController::class);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', LogoutController::class);
        Route::get('me', function (Request $request) {
            return response()->json($request->user()->only(['id', 'name', 'email', 'created_at']));
        });
    });
});

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->group(function () {

    // Styles catalogue
    Route::get('styles', [LandingPageStyleController::class, 'index']);

    // Landing Pages CRUD
    Route::get('landing-pages', [LandingPageController::class, 'index']);
    Route::post('landing-pages', [LandingPageController::class, 'store']);

    Route::middleware('owns.landing_page')->group(function () {
        Route::get('landing-pages/{landingPage}', [LandingPageController::class, 'show']);
        Route::put('landing-pages/{landingPage}', [LandingPageController::class, 'update']);
        Route::delete('landing-pages/{landingPage}', [LandingPageController::class, 'destroy']);

        // AI generation & editing
        Route::post('landing-pages/{landingPage}/generate-variants', [LandingPageEditorController::class, 'generateVariants']);
        Route::post('landing-pages/{landingPage}/select-variant', [LandingPageEditorController::class, 'selectVariant']);
        Route::post('landing-pages/{landingPage}/publish', [LandingPageEditorController::class, 'publish']);
        Route::post('landing-pages/{landingPage}/ai-edit', [LandingPageEditorController::class, 'aiEdit']);

        // Leads
        Route::get('landing-pages/{landingPage}/leads', [LeadController::class, 'index']);
    });

    Route::get('leads/{lead}', [LeadController::class, 'show']);
    Route::patch('leads/{lead}/status', [LeadController::class, 'updateStatus']);
    Route::post('leads/{lead}/notes', [LeadController::class, 'addNote']);
});

/*
|--------------------------------------------------------------------------
| Public: Accept lead form submission
|--------------------------------------------------------------------------
*/
Route::post('submit/{token}', [LeadController::class, 'submit'])
    ->middleware('throttle:10,1');
