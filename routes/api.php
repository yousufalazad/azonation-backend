<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Org\OrganisationController;
use App\Http\Controllers\Individual\IndividualController;
use App\Http\Controllers\Org\OrgMemberListController;
use App\Http\Controllers\Org\CommitteeNameController;
use App\Http\Controllers\Org\MeetingController;
use App\Http\Controllers\Org\OrgAddressController;
use App\Http\Controllers\Org\OrgAdministratorController;
use App\Http\Controllers\Org\OrgPhoneNumberController;
use App\Http\Controllers\Org\OrgEventController;
use App\Http\Controllers\Org\OrgProjectController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrgProfileController;

//API for auth
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

//API for individuals
//Route::resource('individual_data', IndividualController::class);
Route::get('/individual_profile_data/{userId}', [IndividualController::class, 'getProfileImage']);
Route::get('/profileimage/{userId}', [IndividualController::class, 'getProfileImage']);
Route::post('/profileimage/{userId}', [IndividualController::class, 'updateProfileImage']);
Route::get('/connected-org-list/{userId}', [IndividualController::class, 'getOrganisationByIndividualId']);


//API for org profile information
Route::get('/org-profile/{orgId}', [OrgProfileController::class, 'show']);
Route::put('/org-profile/{id}', [OrgProfileController::class, 'update']);
Route::get('/org-profile-data/{userId}', [OrgProfileController::class, 'getOrgData']);


//API for SuperAdmin
// Registration from AuthController
Route::get('/super_admin_user_data/{id}', [SuperAdminController::class, 'show']);

//ORG 

//Notification
// Route::get('/mark-as-read', [OrgMemberListController::class,'markAsRead']);
// Route::get('/notifications/unread/{userId}', [OrgMemberListController::class,'getUnreadNotifications']);
// Route::post('/notifications/mark-as-read/{orgId}', [OrgMemberListController::class, 'markAsRead']);

//Notification
// Route::get('/mark-as-read', [NotificationController::class,'markAsRead']);
// Fetch notifications for a specific user
Route::get('/notifications/get-all/{userId}', [NotificationController::class, 'getNotifications']);
// Mark all notifications as read for a specific user
Route::get('/notifications/mark-all-as-read/{userId}', [NotificationController::class, 'markAllAsRead']);
// Mark all notifications as read for a specific user
Route::get('/notifications/mark-as-read/{userId}/{notificationId}', [NotificationController::class, 'markAsRead']);


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
Route::get('/organisation-address/{id}', [OrgAddressController::class, 'show']);
Route::put('/organisation-address/{id}', [OrgAddressController::class, 'update']);

//API for org phone number
Route::get('/org-phone-number/{id}', [OrgPhoneNumberController::class, 'show']);
Route::put('/org-phone-number/{id}', [OrgPhoneNumberController::class, 'update']);



//API for
Route::resource('org_member_list', OrgMemberListController::class);
Route::get('/organisation/logo/{orgId}', [OrganisationController::class, 'getLogo']);
Route::post('/organisation/logo/{orgId}', [OrganisationController::class, 'updateLogo']);


Route::post('create_committee_store', [CommitteeNameController::class, 'store']);
Route::get('org-committee-list/{userId}', [CommitteeNameController::class, 'getCommitteeListByUserId']);

Route::post('create-meeting-store', [MeetingController::class, 'store']);
Route::get('meeting-list/{orgId}', [MeetingController::class, 'index']);

Route::post('create-event', [OrgEventController::class, 'store']);
Route::get('org-event-list/{orgId}', [OrgEventController::class, 'index']);

Route::get('org-project-list/{orgId}', [OrgProjectController::class, 'index']);
Route::post('create-project', [OrgProjectController::class, 'store']);

//API for superadmin
