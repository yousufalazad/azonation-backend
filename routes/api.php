<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Individual\IndividualController;
use App\Http\Controllers\Org\OrgMemberListController;
use App\Http\Controllers\Org\CommitteeController;
use App\Http\Controllers\Org\MeetingController;
use App\Http\Controllers\Org\AddressController;
use App\Http\Controllers\Org\OrgAdministratorController;
use App\Http\Controllers\Org\PhoneNumberController;
use App\Http\Controllers\Org\OrgEventController;
use App\Http\Controllers\Org\OrgProjectController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Org\OrgProfileController;

//API for auth
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::put('update-username/{userId}', [AuthController::class, 'userNameUpdate']);
Route::put('update-email/{userId}', [AuthController::class, 'userEmailUpdate']);

//API for individuals
//Route::resource('individual_data', IndividualController::class);
Route::get('/individual_profile_data/{userId}', [IndividualController::class, 'getProfileImage']);
Route::get('/profileimage/{userId}', [IndividualController::class, 'getProfileImage']);
Route::post('/profileimage/{userId}', [IndividualController::class, 'updateProfileImage']);
Route::get('/connected-org-list/{userId}', [IndividualController::class, 'getOrganisationByIndividualId']);


//API for SuperAdmin
// Registration from AuthController
Route::get('/super_admin_user_data/{id}', [SuperAdminController::class, 'show']);

//ORG 

//Notification
// Fetch notifications for a specific user
Route::get('/notifications/get-all/{userId}', [NotificationController::class, 'getNotifications']);
// Mark all notifications as read for a specific user
Route::get('/notifications/mark-all-as-read/{userId}', [NotificationController::class, 'markAllAsRead']);
// Mark a notifications as read for a specific user
Route::get('/notifications/mark-as-read/{userId}/{notificationId}', [NotificationController::class, 'markAsRead']);

//API for org profile information
Route::get('/org-profile-data/{userId}', [OrgProfileController::class, 'index']);
Route::put('/org-profile-update/{userId}', [OrgProfileController::class, 'update']);
Route::post('/org-profile/logo/{userId}', [OrgProfileController::class, 'updateLogo']);
Route::get('/org-profile/logo/{userId}', [OrgProfileController::class, 'getLogo']);


//API for org membership
Route::post('/search_individual', [OrgMemberListController::class, 'search']);
Route::post('/add_member', [OrgMemberListController::class, 'addMember']);
Route::get('/org-member-list/{userId}', [OrgMemberListController::class, 'getMemberList']);
Route::get('/total-org-member-count/{userId}', [OrgMemberListController::class, 'totalOrgMemberCount']);


//for Org Administrator
Route::post('/search-individual', [OrgAdministratorController::class, 'search']);
Route::post('/add_administrator', [OrgAdministratorController::class, 'store']);
Route::get('/org-administrator/{orgId}', [OrgAdministratorController::class, 'show']);
Route::put('/update-administrator/{orgId}', [OrgAdministratorController::class, 'update']);


//API for org address
Route::get('address/{userId}', [AddressController::class, 'show']);
Route::post('address', [AddressController::class, 'store']);
Route::put('address/{userId}', [AddressController::class, 'update']);


//API for org phone number
Route::get('phone-number/{userId}', [PhoneNumberController::class, 'show']);
Route::put('phone-number/{userId}', [PhoneNumberController::class, 'update']);


//Committee
Route::post('create_committee', [CommitteeController::class, 'store']);
Route::put('update_committee/{id}', [CommitteeController::class, 'update']);
Route::get('org-committee-list/{userId}', [CommitteeController::class, 'getCommitteeListByUserId']);

//Meeting
Route::post('create-meeting', [MeetingController::class, 'store']);
Route::get('meeting-list/{orgId}', [MeetingController::class, 'index']);

//Event
Route::post('create-event', [OrgEventController::class, 'store']);
Route::get('org-event-list/{userId}', [OrgEventController::class, 'index']);


//Project
Route::get('org-project-list/{userId}', [OrgProjectController::class, 'index']);
Route::post('create-project', [OrgProjectController::class, 'store']);
Route::put('update-project/{id}', [OrgProjectController::class, 'update']);

//API for superadmin
