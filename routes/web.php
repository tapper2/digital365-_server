<?php

use App\Http\Controllers\PublicLandingPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['app' => 'Digital365 API', 'version' => '1.0']);
});

Route::get('/p/{token}', [PublicLandingPageController::class, 'show']);
