<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\IndividualController;
use App\Http\Controllers\OrgLogoController;
use App\Http\Controllers\OrgMemberListController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('org_register', [AuthController::class, 'orgRegister']);
Route::post('individual_register', [AuthController::class, 'individualRegister']);
Route::post('login', [AuthController::class, 'login']);
Route::resource('organisation_data', OrganisationController::class);
Route::resource('individual_data', IndividualController::class);
Route::resource('org_member_list', OrgMemberListController::class);
Route::post('/org_logo', [OrgLogoController::class, 'store']);
Route::post('/search_individuals', [IndividualController::class, 'search']);
Route::post('/add_member', [IndividualController::class, 'addMember']);

Route::get('/org-members-list/{orgId}', [OrgMemberListController::class, 'getMembersByOrgId']);

   