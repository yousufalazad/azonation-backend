<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\IndividualController;
use App\Http\Controllers\OrgMemberListController;
use App\Http\Controllers\CommitteeNameController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('org_register', [AuthController::class, 'orgRegister']);
Route::post('individual_register', [AuthController::class, 'individualRegister']);
Route::post('login', [AuthController::class, 'login']);

Route::post('/search_individuals', [IndividualController::class, 'search']);
Route::post('/add_member', [IndividualController::class, 'addMember']);
Route::resource('individual_data', IndividualController::class);
Route::get('/profileimage/{individualId}', [IndividualController::class, 'getProfileImage']);
Route::post('/profileimage/{individualId}', [IndividualController::class, 'updateProfileImage']);
Route::get('/connected-org-list/{individualId}', [IndividualController::class, 'getOrganisationByIndividualId']);

Route::resource('organisation_data', OrganisationController::class);
Route::resource('org_member_list', OrgMemberListController::class);
Route::get('/organisation/logo/{orgId}', [OrganisationController::class, 'getLogo']);
Route::post('/organisation/logo/{orgId}', [OrganisationController::class, 'updateLogo']);
Route::get('/org-members-list/{orgId}', [OrgMemberListController::class, 'getMembersByOrgId']);

Route::post('create_committee_store', [CommitteeNameController::class, 'committeeStore']);
Route::get('org-committee-list/{orgId}', [CommitteeNameController::class, 'getCommitteeListByOrgId']);
