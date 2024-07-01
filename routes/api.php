<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\IndividualController;
use App\Http\Controllers\OrgMemberListController;
use App\Http\Controllers\CommitteeNameController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\OrgAddressController;
use App\Http\Controllers\OrgAdministratorController;
use App\Http\Controllers\OrgPhoneNumberController;
use App\Http\Controllers\OrgEventController;
use App\Http\Controllers\OrgProjectController;
use App\Http\Controllers\SuperAdminController;
use App\Models\OrgAdministrator;
use GuzzleHttp\Promise\Create;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

//API for auth
Route::post('individual_register', [AuthController::class, 'individualRegister']);
Route::post('org_register', [AuthController::class, 'orgRegister']);
Route::post('superadmin_register', [AuthController::class, 'superAdminRegister']);
Route::post('login', [AuthController::class, 'login']);

//API for individuals
Route::resource('individual_data', IndividualController::class);
Route::get('/profileimage/{individualId}', [IndividualController::class, 'getProfileImage']);
Route::post('/profileimage/{individualId}', [IndividualController::class, 'updateProfileImage']);
Route::get('/connected-org-list/{individualId}', [IndividualController::class, 'getOrganisationByIndividualId']);


//API for org profile information
Route::resource('organisation_data', OrganisationController::class);
Route::get('/organisation/{id}', [OrganisationController::class, 'index']);
Route::put('/organisation/{id}', [OrganisationController::class, 'update']);

//API for SuperAdmin
// Route::post('/superadmin_register', [SuperAdminController::class, 'index']);

//API for org membership
Route::post('/search_org_members', [OrgMemberListController::class, 'search']);
Route::post('/add_member', [OrgMemberListController::class, 'addMember']);
Route::get('/org-members-list/{orgId}', [OrgMemberListController::class, 'getMembersByOrgId']);
Route::get('/total-org-member-count/{orgId}', [OrgMemberListController::class, 'totalOrgMemberCount']);


//for Org Administrator
Route::post('/search-individual', [OrgAdministratorController::class, 'search']);
Route::post('/add_administrator', [OrgAdministratorController::class, 'store']);
Route::get('/org-administrator/{orgId}', [OrgAdministratorController::class, 'show']);
Route::put('/update-administrator/{orgId}', [OrgAdministratorController::class, 'update']);


//API for org address
Route::get('/organisation-address/{id}', [OrgAddressController::class, 'show']);
Route::put('/organisation-address/{id}', [OrgAddressController::class, 'update']);

//API for org phone number
Route::get('/org-phone-number/{id}', [OrgPhoneNumberController::class, 'show']);
Route::put('/org-phone-number/{id}', [OrgPhoneNumberController::class, 'update']);



//API for
Route::resource('org_member_list', OrgMemberListController::class);
Route::get('/organisation/logo/{orgId}', [OrganisationController::class, 'getLogo']);
Route::post('/organisation/logo/{orgId}', [OrganisationController::class, 'updateLogo']);


Route::post('create_committee_store', [CommitteeNameController::class, 'committeeStore']);
Route::get('org-committee-list/{orgId}', [CommitteeNameController::class, 'getCommitteeListByOrgId']);

Route::post('create-meeting-store', [MeetingController::class, 'store']);
Route::get('meeting-list/{orgId}', [MeetingController::class, 'index']);

Route::post('create-event', [OrgEventController::class, 'store']);
Route::get('org-event-list/{orgId}', [OrgEventController::class, 'index']);

Route::get('org-project-list/{orgId}', [OrgProjectController::class, 'index']);
Route::post('create-project', [OrgProjectController::class, 'store']);

//API for superadmin
