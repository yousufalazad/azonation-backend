<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialAuthController;


Route::get('/', fn () => response()->json([
    'app' => config('app.name'),
    'status' => 'ok',
]));

// Google OAuth
Route::prefix('auth/google')->group(function () {
    Route::get('/redirect', [SocialAuthController::class, 'redirectToGoogle'])->name('oauth.google.redirect');
    Route::get('/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('oauth.google.callback');
});

