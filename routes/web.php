<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

Route::get('/', fn () => response()->json([
    'app' => config('app.name'),
    'status' => 'ok',
]));

// Google OAuth
Route::prefix('auth/google')->group(function () {
    Route::get('/redirect', [SocialAuthController::class, 'redirectToGoogle'])->name('oauth.google.redirect');
    Route::get('/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('oauth.google.callback');
});



Route::get('/auth/ops/refresh-cache', function (Request $request) {
    // Only allow in production/staging (change as you like)
    if (! app()->environment(['production', 'staging'])) {
        abort(403, 'Disabled in this environment.');
    }

    // Verify secret
    $key = env('DEPLOY_CACHE_KEY');
    $provided = (string) $request->query('key', '');
    if (! $key || ! hash_equals($key, $provided)) {
        abort(403, 'Unauthorized.');
    }

    $results = [];

    // Clear everything first
    foreach ([
        'config:clear',
        'cache:clear',
        'route:clear',
        'view:clear',
        'event:clear',
        'optimize:clear',
    ] as $cmd) {
        $code = Artisan::call($cmd);
        $results[] = ['cmd' => $cmd, 'exit' => $code, 'output' => trim(Artisan::output())];
    }

    // Re-cache config/routes (optional but recommended for prod)
    foreach (['config:cache', 'route:cache', 'view:cache'] as $cmd) {
        $code = Artisan::call($cmd);
        $results[] = ['cmd' => $cmd, 'exit' => $code, 'output' => trim(Artisan::output())];
    }

    // Reset PHP OpCache if available
    $opcache = function_exists('opcache_reset') && opcache_reset();

    return response()->json([
        'ok'       => true,
        'env'      => app()->environment(),
        'time'     => now()->toDateTimeString(),
        'opcache'  => $opcache ? 'reset' : 'not-available',
        'results'  => $results,
    ]);
})->name('ops.refresh-cache');

