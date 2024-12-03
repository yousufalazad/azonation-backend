<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BillingController;

Route::get('/test-billing', [BillingController::class, 'storeBySystem']);
