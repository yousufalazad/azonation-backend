<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('org_register', [AuthController::class, 'orgRegister']);
Route::post('individual_register', [AuthController::class, 'individualRegister']);
Route::post('login', [AuthController::class, 'login']);

