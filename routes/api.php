<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Individual\IndividualController;
use App\Http\Controllers\Org\OrgMemberListController;
use App\Http\Controllers\Org\CommitteeController;
use App\Http\Controllers\Org\MeetingController;
use App\Http\Controllers\Org\AddressController;
use App\Http\Controllers\Org\OrgAdministratorController;
use App\Http\Controllers\PhoneNumberController;
use App\Http\Controllers\Org\OrgEventController;
use App\Http\Controllers\Org\OrgProjectController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Org\FounderController;
use App\Http\Controllers\Org\OrgProfileController;

use App\Http\Controllers\SuccessStoryController;
use App\Http\Controllers\StrategicPlanController;
use App\Http\Controllers\OrgHistoryController;
use App\Http\Controllers\YearPlanController;
use App\Http\Controllers\OrgRecognitionController;
use App\Http\Controllers\OrgAccountController;
use App\Http\Controllers\OrgReportController;

//Org office record
use App\Http\Controllers\OrgOfficeRecordController;


//Accounts


// Group routes for authenticated users
Route::middleware('auth:sanctum')->group(function () {
    // Fetch all transactions
    Route::get('/get-transactions', [OrgAccountController::class, 'getTransactions']);
    // Create a new transaction
    Route::post('/create-transaction', [OrgAccountController::class, 'createTransaction']);
    // Update an existing transaction
    Route::put('/update-transaction/{id}', [OrgAccountController::class, 'updateTransaction']);
    // Delete a transaction
    Route::delete('/delete-transaction/{id}', [OrgAccountController::class, 'deleteTransaction']);
});


// OrgHistoryController
Route::get('/get-org-histories', [OrgHistoryController::class, 'index']);
Route::post('/create-org-history', [OrgHistoryController::class, 'store']);
Route::put('/update-org-history/{id}', [OrgHistoryController::class, 'update']);
Route::delete('/delete-org-history/{id}', [OrgHistoryController::class, 'destroy']);

//Year plan
    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/get-year-plans', [YearPlanController::class, 'index']);
    Route::get('/year-plan/{id}', [YearPlanController::class, 'show']);
    Route::post('/create-year-plan', [YearPlanController::class, 'store']);
    Route::put('/update-year-plan/{id}', [YearPlanController::class, 'update']);
    Route::delete('/delete-year-plan/{id}', [YearPlanController::class, 'destroy']);
});

// OrgRecognitionController
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/get-recognitions', [OrgRecognitionController::class, 'index']);
    Route::post('/create-recognition', [OrgRecognitionController::class, 'store']);
    Route::put('/update-recognition/{id}', [OrgRecognitionController::class, 'update']);
    Route::delete('/delete-recognition/{id}', [OrgRecognitionController::class, 'destroy']);
});

// StrategicPlan
    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/get-strategic-plans', [StrategicPlanController::class, 'index']);
    Route::post('/create-strategic-plan', [StrategicPlanController::class, 'store']);
    Route::put('/update-strategic-plan/{id}', [StrategicPlanController::class, 'update']);
    Route::delete('/delete-strategic-plan/{id}', [StrategicPlanController::class, 'destroy']);
});

// SuccessStoryController
Route::get('/get-records', [SuccessStoryController::class, 'index']);
Route::post('/create-record', [SuccessStoryController::class, 'store']);
Route::put('/update-record/{id}', [SuccessStoryController::class, 'update']);
Route::delete('/delete-record/{id}', [SuccessStoryController::class, 'destroy']);

//API for auth
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::put('update-name/{userId}', [AuthController::class, 'nameUpdate']);
Route::put('update-username/{userId}', [AuthController::class, 'usernameUpdate']);
Route::put('update-email/{userId}', [AuthController::class, 'userEmailUpdate']);
Route::put('update-password/{userId}', [AuthController::class, 'updatePassword']);

//User data
// Route::get('/user-data-local-update/{userId}', [AuthController::class, 'getUserDataLocalUpdate']);

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

//Reporting
Route::get('/reports', [OrgReportController::class, 'getIncomeReport']);
Route::get('/org-expense-reports', [OrgReportController::class, 'getExpenseReport']);



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

//Office record
Route::get('/get-office-records', [OrgOfficeRecordController::class, 'index']);
Route::post('/create-office-record', [OrgOfficeRecordController::class, 'store']);
Route::put('/update-office-record/{id}', [OrgOfficeRecordController::class, 'update']);
Route::delete('/delete-office-record/{id}', [OrgOfficeRecordController::class, 'destroy']);


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


//API for phone number
Route::get('phone-number/{userId}', [PhoneNumberController::class, 'show']);
Route::put('phone-number/{userId}', [PhoneNumberController::class, 'update']);
Route::get('dialing-codes', [PhoneNumberController::class, 'getAllDialingCodes']);


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
Route::put('update-project/{userId}', [OrgProjectController::class, 'update']);

// Founder
Route::post('create-founder', [FounderController::class, 'store']);
Route::get('get-founder/{userId}', [FounderController::class, 'index']);
Route::put('update-founder/{id}', [FounderController::class, 'update']);



//API for superadmin
