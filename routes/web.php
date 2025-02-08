<?php

// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Individual\IndividualController;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

Route::middleware([
    EnsureFrontendRequestsAreStateful::class,
    'auth:sanctum'
])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();  // Should return authenticated user if session is valid
    });

    Route::post('logout', [AuthController::class, 'logout']);

    // ------------------- Individual----------------------------------------------------------------
    Route::get('/individual_profile_data/{userId}', [IndividualController::class, 'getProfileImage']);
    Route::get('/profileimage/{userId}', [IndividualController::class, 'getProfileImage']);
    Route::post('/profileimage/{userId}', [IndividualController::class, 'updateProfileImage']);
    Route::get('/connected-org-list/{userId}', [IndividualController::class, 'getOrganisationByIndividualId']);
    Route::get('/individual-users', [IndividualController::class, 'getIndividualUser']);
});
