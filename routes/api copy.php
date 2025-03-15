<?php

use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\Auth\AuthController;

// ------------------------------Common----------------------------------------------------------
use App\Http\Controllers\Common\UserCountryController;
use App\Http\Controllers\Common\AddressController;
use App\Http\Controllers\Common\PhoneNumberController;
use App\Http\Controllers\Common\NotificationController;

// ------------------------------E-commerce-------------------------------------------------------

// E-commerce\Category
use App\Http\Controllers\Ecommerce\Category\BusinessTypeController;
use App\Http\Controllers\Ecommerce\Category\CategoryController;
use App\Http\Controllers\Ecommerce\Category\SubCategoryController;
use App\Http\Controllers\Ecommerce\Category\SubSubCategoryController;

// Ecommerce \\ brand
use App\Http\Controllers\Ecommerce\BrandController;

// Ecommerce\Product
use App\Http\Controllers\Ecommerce\Product\ProductController;

// Ecommerce\Order
use App\Http\Controllers\Ecommerce\Order\OrderItemController;
use App\Http\Controllers\Ecommerce\Order\OrderController;
use App\Http\Controllers\Ecommerce\Order\OrderDetailController;


// ----------------------------------Individual----------------------------------------------------
use App\Http\Controllers\Individual\IndividualController;

// --------------- Organisation -------------------------------------------------------------------
// Org\Account
use App\Http\Controllers\Org\Account\AccountController;
use App\Http\Controllers\Org\Account\AccountFundController;

// Org\Asset
use App\Http\Controllers\Org\Asset\AssetController;
use App\Http\Controllers\Org\Asset\AssetLifecycleStatusController;

// Org\Committee
use App\Http\Controllers\Org\Committee\CommitteeController;
use App\Http\Controllers\Org\Committee\CommitteeMemberController;

// Org\Event
use App\Http\Controllers\Org\Event\EventController;
use App\Http\Controllers\Org\Event\EventAttendanceController;
use App\Http\Controllers\Org\Event\EventSummaryController;
use App\Http\Controllers\Org\Event\EventGuestAttendanceController;

// Org\History
use App\Http\Controllers\Org\History\HistoryController;

// Org\Meeting
use App\Http\Controllers\Org\Meeting\MeetingController;
use App\Http\Controllers\Org\Meeting\MeetingMinutesController;
use App\Http\Controllers\Org\Meeting\MeetingAttendanceController;
use App\Http\Controllers\Org\Meeting\MeetingGuestAttendanceController;

// Org\Membership
use App\Http\Controllers\Org\Membership\OrgMemberController;
use App\Http\Controllers\Org\Membership\OrgIndependentMemberController;

// Org\OfficeDocument
use App\Http\Controllers\Org\OfficeDocument\OfficeRecordController;

// Org\Project
use App\Http\Controllers\Org\Project\ProjectAttendanceController;
use App\Http\Controllers\Org\Project\ProjectSummaryController;
use App\Http\Controllers\Org\Project\ProjectController;
use App\Http\Controllers\Org\Project\ProjectGuestAttendanceController;

// Org\Recognition
use App\Http\Controllers\Org\Recognition\RecognitionController;

// Org\Report
use App\Http\Controllers\Org\Report\OrgReportController;

// Org\StrategicPlan
use App\Http\Controllers\Org\StrategicPlan\StrategicPlanController;

// Org\SuccessStory
use App\Http\Controllers\Org\SuccessStory\SuccessStoryController;

// Org\YearPlan
use App\Http\Controllers\Org\YearPlan\YearPlanController;

// Org
use App\Http\Controllers\Org\FounderController;
use App\Http\Controllers\Org\OrgAdministratorController;
use App\Http\Controllers\Org\OrgProfileController;

// ------------------------------Super Admin---------------------------------------------------------
// SuperAdmin
use App\Http\Controllers\SuperAdmin\SuperAdminController;

// SuperAdmin\Settings
use App\Http\Controllers\SuperAdmin\Settings\AttendanceTypeController;
use App\Http\Controllers\SuperAdmin\Settings\ConductTypeController;
use App\Http\Controllers\SuperAdmin\Settings\CountryController;
use App\Http\Controllers\SuperAdmin\Settings\CountryRegionController;
use App\Http\Controllers\SuperAdmin\Settings\CurrencyController;
use App\Http\Controllers\SuperAdmin\Settings\DesignationController;
use App\Http\Controllers\SuperAdmin\Settings\DialingCodeController;
use App\Http\Controllers\SuperAdmin\Settings\LanguageListController;
use App\Http\Controllers\SuperAdmin\Settings\MembershipTypeController;
use App\Http\Controllers\SuperAdmin\Settings\PrivacySetupController;
use App\Http\Controllers\SuperAdmin\Settings\RegionController;
use App\Http\Controllers\SuperAdmin\Settings\RegionCurrencyController;
use App\Http\Controllers\SuperAdmin\Settings\TimeZoneSetupController;

// SuperAdmin\Financial \\ Invoices
use App\Http\Controllers\SuperAdmin\Financial\InvoiceController;

// Tax
use App\Http\Controllers\SuperAdmin\Financial\RegionalTaxRateController;

// SuperAdmin\Financial\Management
use App\Http\Controllers\SuperAdmin\Financial\Management\EverydayMemberCountAndBillingController;
use App\Http\Controllers\SuperAdmin\Financial\Management\ManagementAndStorageBillingController;
use App\Http\Controllers\SuperAdmin\Financial\Management\ManagementPricingController;
use App\Http\Controllers\SuperAdmin\Financial\Management\ManagementSubscriptionController;

// SuperAdmin\Financial\Storage
use App\Http\Controllers\SuperAdmin\Financial\Storage\EverydayStorageBillingController;

// ==================================API Routes=======================================================

//API for auth
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('/verify-account/{uuid}', [AuthController::class, 'verify']);


Route::middleware('auth:sanctum')->group(function () {

    //-------------------- Common--------------------------------------------------------------------
    Route::post('logout', [AuthController::class, 'logout']);

    //addresses
    //group name: addresses
    // Route::get('address/{userId}', [AddressController::class, 'show']);
    // Route::post('address', [AddressController::class, 'store']);
    // Route::put('address/{userId}', [AddressController::class, 'update']);

    Route::group(['prefix' => 'addresses'], function () {
        Route::get('/', [AddressController::class, 'show']); // Get address details by user ID
        Route::post('/', [AddressController::class, 'store']); // Create a new address
        Route::put('/', [AddressController::class, 'update']); // Update address by user ID
    });
    

    //API for phone number
    //group name: phone-numbers
    // Route::get('phone-number/{userId}', [PhoneNumberController::class, 'show']);
    // Route::put('phone-number/{userId}', [PhoneNumberController::class, 'update']);
    // Route::get('dialing-codes', [PhoneNumberController::class, 'getAllDialingCodes']);

    Route::group(['prefix' => 'phone-numbers'], function () {
        Route::get('/', [PhoneNumberController::class, 'show']); // Get phone number details by user ID
        Route::put('/', [PhoneNumberController::class, 'update']); // Update phone number by user ID
        Route::get('/dialing-codes', [PhoneNumberController::class, 'getAllDialingCodes']); // Get all dialing codes
    });

    
    //update user info  
    //group name: no need
    Route::put('update-name/{userId}', [AuthController::class, 'nameUpdate']);
    Route::put('update-username/{userId}', [AuthController::class, 'usernameUpdate']);
    Route::put('update-email/{userId}', [AuthController::class, 'userEmailUpdate']);
    Route::put('update-password/{userId}', [AuthController::class, 'updatePassword']);

    // ------------------- Organisation----------------------------------------------------------------
    //Billing
    //group name: org-billing
    Route::get('org-all-bill', [ManagementAndStorageBillingController::class, 'orgAllBill']);

    // Accounts
    //group name: transactions
    // Route::get('/get-transactions', [AccountController::class, 'getTransactions']);
    // Route::post('/create-transaction', [AccountController::class, 'createTransaction']);
    // Route::put('/update-transaction/{id}', [AccountController::class, 'updateTransaction']);
    // Route::delete('/delete-transaction/{id}', [AccountController::class, 'deleteTransaction']);

    Route::group(['prefix' => 'transactions'], function () {
        Route::get('/', [AccountController::class, 'index']); // Get all transactions
        Route::post('/', [AccountController::class, 'store']); // Create a new transaction
        Route::put('/{id}', [AccountController::class, 'update']); // Update a specific transaction
        Route::delete('/{id}', [AccountController::class, 'destroy']); // Delete a specific transaction
    });
    
    // Funds
    //group name: funds
    // Route::get('/get-funds', [AccountFundController::class, 'index']);
    // Route::post('/create-fund', [AccountFundController::class, 'store']);
    // Route::put('/update-fund/{id}', [AccountFundController::class, 'update']);
    // Route::delete('/delete-fund/{id}', [AccountFundController::class, 'destroy']);

    Route::group(['prefix' => 'funds'], function () {
        Route::get('/', [AccountFundController::class, 'index']); // Get all funds
        Route::post('/', [AccountFundController::class, 'store']); // Create a new fund
        Route::put('/{id}', [AccountFundController::class, 'update']); // Update a specific fund
        Route::delete('/{id}', [AccountFundController::class, 'destroy']); // Delete a specific fund
    });
    

    // HistoryController
    //group name: histories
    // Route::get('/get-org-histories', [HistoryController::class, 'index']);
    // Route::get('/get-org-history/{id}', [HistoryController::class, 'show']);
    // Route::post('/create-org-history', [HistoryController::class, 'store']);
    // Route::put('/update-org-history/{id}', [HistoryController::class, 'update']);
    // Route::delete('/delete-org-history/{id}', [HistoryController::class, 'destroy']);

    Route::group(['prefix' => 'histories'], function () {
        Route::get('/', [HistoryController::class, 'index']); // Get all organization histories
        Route::get('/{id}', [HistoryController::class, 'show']); // Get a specific organization history by ID
        Route::post('/', [HistoryController::class, 'store']); // Create a new organization history
        Route::post('/{id}', [HistoryController::class, 'update']); // Update a specific organization history
        Route::delete('/{id}', [HistoryController::class, 'destroy']); // Delete a specific organization history
    });
    
    //Year plan
    //group name: year-plans
    // Route::get('/get-year-plans', [YearPlanController::class, 'index']);
    // Route::get('/year-plan/{id}', [YearPlanController::class, 'show']);
    // Route::post('/create-year-plan', [YearPlanController::class, 'store']);
    // Route::post('/update-year-plan/{id}', [YearPlanController::class, 'update']);
    // Route::delete('/delete-year-plan/{id}', [YearPlanController::class, 'destroy']);

    Route::group(['prefix' => 'year-plans'], function () {
        Route::get('/', [YearPlanController::class, 'index']); // Get all year plans
        Route::get('/{id}', [YearPlanController::class, 'show']); // Get a specific year plan by ID
        Route::post('/', [YearPlanController::class, 'store']); // Create a new year plan
        Route::post('/{id}', [YearPlanController::class, 'update']); // Update a specific year plan
        Route::delete('/{id}', [YearPlanController::class, 'destroy']); // Delete a specific year plan
    });
    
    // Recognition
    //group name: recognitions
    // Route::get('/get-recognitions', [RecognitionController::class, 'index']);
    // Route::get('/get-recognition/{id}', [RecognitionController::class, 'show']);
    // Route::post('/create-recognition', [RecognitionController::class, 'store']);
    // Route::put('/update-recognition/{id}', [RecognitionController::class, 'update']);
    // Route::delete('/delete-recognition/{id}', [RecognitionController::class, 'destroy']);

    Route::group(['prefix' => 'recognitions'], function () {
        Route::get('/', [RecognitionController::class, 'index']); // Get all recognitions
        Route::get('/{id}', [RecognitionController::class, 'show']); // Get a specific recognition by ID
        Route::post('/', [RecognitionController::class, 'store']); // Create a new recognition
        Route::put('/{id}', [RecognitionController::class, 'update']); // Update a specific recognition
        Route::delete('/{id}', [RecognitionController::class, 'destroy']); // Delete a specific recognition
    });
    

    // StrategicPlan
    //group name: strategic-plans
    // Route::get('/get-strategic-plans', [StrategicPlanController::class, 'index']);
    // Route::get('/get-strategic-plan/{id}', [StrategicPlanController::class, 'show']);
    // Route::post('/create-strategic-plan', [StrategicPlanController::class, 'store']);
    // Route::put('/update-strategic-plan/{id}', [StrategicPlanController::class, 'update']);
    // Route::delete('/delete-strategic-plan/{id}', [StrategicPlanController::class, 'destroy']);
    
    Route::group(['prefix' => 'strategic-plans'], function () {
        Route::get('/', [StrategicPlanController::class, 'index']); // Get all strategic plans
        Route::get('/{id}', [StrategicPlanController::class, 'show']); // Get a specific strategic plan by ID
        Route::post('/', [StrategicPlanController::class, 'store']); // Create a new strategic plan
        Route::put('/{id}', [StrategicPlanController::class, 'update']); // Update a specific strategic plan
        Route::delete('/{id}', [StrategicPlanController::class, 'destroy']); // Delete a specific strategic plan
    });
    
    // SuccessStoryController
    //group name: success-stories
    // Route::get('/get-records', [SuccessStoryController::class, 'index']);
    // Route::get('/get-record/{id}', [SuccessStoryController::class, 'show']);
    // Route::post('/create-record', [SuccessStoryController::class, 'store']);
    // Route::put('/update-record/{id}', [SuccessStoryController::class, 'update']);
    // Route::delete('/delete-record/{id}', [SuccessStoryController::class, 'destroy']);

    Route::group(['prefix' => 'success-stories'], function () {
        Route::get('/', [SuccessStoryController::class, 'index']); // Get all success stories
        Route::get('/{id}', [SuccessStoryController::class, 'show']); // Get a specific success story by ID
        Route::post('/', [SuccessStoryController::class, 'store']); // Create a new success story
        Route::put('/{id}', [SuccessStoryController::class, 'update']); // Update a specific success story
        Route::delete('/{id}', [SuccessStoryController::class, 'destroy']); // Delete a specific success story
    });    

    //Reporting
    //group name: no need
    Route::get('/reports', [OrgReportController::class, 'getIncomeReport']);
    Route::get('/org-expense-reports', [OrgReportController::class, 'getExpenseReport']);

    //Notification
    //group name: no need
    // Fetch notifications for a specific user
    Route::get('/notifications/get-all/{userId}', [NotificationController::class, 'getNotifications']);
    // Mark all notifications as read for a specific user
    Route::get('/notifications/mark-all-as-read/{userId}', [NotificationController::class, 'markAllAsRead']);
    // Mark a notifications as read for a specific user
    Route::get('/notifications/mark-as-read/{userId}/{notificationId}', [NotificationController::class, 'markAsRead']);


    //API for org profile information
    //group name: org-profile-logo
    Route::get('/org-profile-data/{userId}', [OrgProfileController::class, 'index']);
    Route::put('/org-profile-update/{userId}', [OrgProfileController::class, 'update']);
    Route::post('/org-profile/logo/{userId}', [OrgProfileController::class, 'updateLogo']);
    Route::get('/org-profile/logo/{userId}', [OrgProfileController::class, 'getLogo']);

    // Route::group(['prefix' => 'org-profile-logo'], function () {
    //     Route::get('/data/{userId}', [OrgProfileController::class, 'index']); // Get org profile data
    //     Route::put('/update/{userId}', [OrgProfileController::class, 'update']); // Update org profile
    //     Route::post('/logo/{userId}', [OrgProfileController::class, 'updateLogo']); // Update org profile logo
    //     Route::get('/logo/{userId}', [OrgProfileController::class, 'getLogo']); // Get org profile logo
    // });
    

    //Office record
    //group name: office-documents (controller change hoye OfficeDocumentController koiro + Model and DB change)
    // Route::get('/get-office-records', [OfficeRecordController::class, 'index']);
    // Route::get('/get-office-record/{recordId}', [OfficeRecordController::class, 'show']);
    // Route::post('/create-office-record', [OfficeRecordController::class, 'store']);
    // Route::put('/update-office-record/{id}', action: [OfficeRecordController::class, 'update']);
    // Route::delete('/delete-office-record/{id}', [OfficeRecordController::class, 'destroy']);

    Route::group(['prefix' => 'office-documents'], function () {
        Route::get('/', [OfficeRecordController::class, 'index']); // Get all office records
        Route::get('/{recordId}', [OfficeRecordController::class, 'show']); // Get a specific office record
        Route::post('/', [OfficeRecordController::class, 'store']); // Create a new office record
        Route::put('/{id}', [OfficeRecordController::class, 'update']); // Update an office record
        Route::delete('/{id}', [OfficeRecordController::class, 'destroy']); // Delete an office record
    });
    

    //API for org membership
    //group name: org-members
    // Route::get('/org-members/{userId}', [OrgMemberController::class, 'getOrgMembers']);
    // Route::post('/search_individual', [OrgMemberController::class, 'search']);
    // Route::post('/add_member', [OrgMemberController::class, 'addMember']); //org-members er sathe conflict kore, org-members er sathe conflict kore but org-members (create) proper mone hocche
    // Route::get('/org-member-list/{userId}', [OrgMemberController::class, 'getMemberList']); //org-members --> index
    // Route::get('/org-all-members', [OrgMemberController::class, 'getOrgAllMembers']);

    Route::group(['prefix' => 'org-members'], function () {
        Route::get('/{userId}', [OrgMemberController::class, 'getOrgMembers']); // Get specific user's org members
        Route::get('/list/{userId}', [OrgMemberController::class, 'getMemberList']); // Get member list (index)
        Route::get('/all', [OrgMemberController::class, 'getOrgAllMembers']); // Get all org members
        Route::post('/search', [OrgMemberController::class, 'search']); // Search for an individual
        Route::post('/create', [OrgMemberController::class, 'addMember']); // Create org member (previous conflict resolved)
    });
    
    //group name: no group
    Route::get('/org-all-member-name', [OrgMemberController::class, 'getOrgAllMemberName']);
    Route::get('/total-org-member-count/{userId}', [OrgMemberController::class, 'totalOrgMemberCount']);

    // OrgIndependentMemberController
    //group name: independent-members
    // Route::get('get-independent-members', [OrgIndependentMemberController::class, 'index']);
    // Route::get('get-independent-member/{id}', [OrgIndependentMemberController::class, 'show']);
    // Route::post('create-independent-member', [OrgIndependentMemberController::class, 'store']);
    // Route::put('update-independent-member/{id}', [OrgIndependentMemberController::class, 'update']);
    // Route::delete('delete-independent-member/{id}', [OrgIndependentMemberController::class, 'destroy']);

    Route::group(['prefix' => 'independent-members'], function () {
        Route::get('/', [OrgIndependentMemberController::class, 'index']); // Get all independent members
        Route::get('/{id}', [OrgIndependentMemberController::class, 'show']); // Get a specific independent member
        Route::post('/', [OrgIndependentMemberController::class, 'store']); // Create an independent member
        Route::put('/{id}', [OrgIndependentMemberController::class, 'update']); // Update an independent member
        Route::delete('/{id}', [OrgIndependentMemberController::class, 'destroy']); // Delete an independent member
    });
    
    //for Org Administrator
    //group name: org-administrators
    // Route::post('/search-individual', [OrgAdministratorController::class, 'search']); //create function e searching korabo, ekhane group name same thakbe
    // Route::post('/add_administrator', [OrgAdministratorController::class, 'store']);
    // Route::get('/org-administrator/{orgId}', [OrgAdministratorController::class, 'show']);
    // Route::put('/update-administrator/{orgId}', [OrgAdministratorController::class, 'update']);

    // not use
    Route::group(['prefix' => 'org-administrators'], function () {
        Route::post('/', [OrgAdministratorController::class, 'index']); // Search function
        Route::post('/', [OrgAdministratorController::class, 'store']); // Add administrator
        Route::get('/{orgId}', [OrgAdministratorController::class, 'show']); // Get specific administrator
        Route::put('/{orgId}', [OrgAdministratorController::class, 'update']); // Update administrator
    });
    

    //Committee
    //group name: committees
    // Route::get('org-committee-list/{userId}', [CommitteeController::class, 'getCommitteeListByUserId']);
    // Route::post('create_committee', [CommitteeController::class, 'store']);
    // Route::put('update_committee/{id}', [CommitteeController::class, 'update']);
    // Route::delete('org-committee/{id}', [CommitteeController::class, 'destroy']);
    Route::group(['prefix' => 'committees'], function () {
        //Route::get('/user/{userId}', [CommitteeController::class, 'index']); 
        Route::get('/', [CommitteeController::class, 'index']); // Get committee list by user ID
        Route::post('/', [CommitteeController::class, 'store']); // Create a new committee
        Route::put('/{id}', [CommitteeController::class, 'update']); // Update a specific committee
        Route::delete('/{id}', [CommitteeController::class, 'destroy']); // Delete a specific committee
    });
    

    //Committee Member
    //group name: committee-members
    // Route::get('/get-committee-members', [CommitteeMemberController::class, 'index']);
    // Route::get('/get-committee-member/{id}', [CommitteeMemberController::class, 'show']);
    // Route::post('/create-committee-member', [CommitteeMemberController::class, 'store']);
    // Route::put('/update-committee-member/{id}', [CommitteeMemberController::class, 'update']);
    // Route::delete('/delete-committee-member/{id}', [CommitteeMemberController::class, 'destroy']);
    Route::group(['prefix' => 'committee-members'], function () {
        Route::get('/', [CommitteeMemberController::class, 'index']); // Get all committee members
        Route::get('/{id}', [CommitteeMemberController::class, 'show']); // Get a specific committee member
        Route::post('/', [CommitteeMemberController::class, 'store']); // Create a new committee member
        Route::put('/{id}', [CommitteeMemberController::class, 'update']); // Update a specific committee member
        Route::delete('/{id}', [CommitteeMemberController::class, 'destroy']); // Delete a specific committee member
    });
    
    //Meeting
    //group name: meetings
    // Route::get('/get-meetings', [MeetingController::class, 'index']);
    // Route::get('/get-meeting/{id}', [MeetingController::class, 'show']); // Fetch meeting details
    // Route::get('/get-org-meetings', [MeetingController::class, 'getOrgMeeting']);
    // Route::post('/create-meeting', [MeetingController::class, 'store']);
    // Route::put('/update-meeting/{id}', [MeetingController::class, 'update']);
    // Route::delete('/delete-meeting/{id}', [MeetingController::class, 'destroy']);
    Route::group(['prefix' => 'meetings'], function () {
        Route::get('/', [MeetingController::class, 'index']); // Fetch meetings for an organization
        Route::get('/{id}', [MeetingController::class, 'show']); // Fetch meeting details
        Route::post('/', [MeetingController::class, 'store']); // Create a new meeting
        Route::put('/{id}', [MeetingController::class, 'update']); // Update a specific meeting
        Route::delete('/{id}', [MeetingController::class, 'destroy']); // Delete a specific meeting
    });

    // meeting-minutes
    //group name: meeting-minutes
    // Route::get('/get-meeting-minutes', [MeetingMinutesController::class, 'index']);
    // Route::get('/get-meeting-minutes/{id}', [MeetingMinutesController::class, 'show']);
    // Route::post('/create-meeting-minutes', [MeetingMinutesController::class, 'store']);
    // Route::post('/update-meeting-minutes/{id}', [MeetingMinutesController::class, 'update']);
    // Route::delete('/delete-meeting-minutes/{id}', [MeetingMinutesController::class, 'destroy']);
    Route::group(['prefix' => 'meeting-minutes'], function () {
        Route::get('/', [MeetingMinutesController::class, 'index']); // Get all meeting minutes
        Route::get('/{id}', [MeetingMinutesController::class, 'show']); // Get a specific meeting minute
        Route::post('/', [MeetingMinutesController::class, 'store']); // Create a new meeting minute
        Route::put('/{id}', [MeetingMinutesController::class, 'update']); // Update a specific meeting minute
        Route::delete('/{id}', [MeetingMinutesController::class, 'destroy']); // Delete a specific meeting minute
    });
    
    //Meeting MeetingAttendance
    //group name: meeting-attendances
    // Route::get('/get-org-user-list', [MeetingAttendanceController::class, 'getOrgUse']);
    // Route::get('/get-meeting-attendances', [MeetingAttendanceController::class, 'index']);
    // Route::get('/get-meeting-attendance/{id}', [MeetingAttendanceController::class, 'show']);
    // Route::post('/create-meeting-attendance', [MeetingAttendanceController::class, 'store']);
    // Route::put('/update-meeting-attendance/{id}', [MeetingAttendanceController::class, 'update']);
    // Route::delete('/delete-meeting-attendance/{id}', [MeetingAttendanceController::class, 'destroy']);
    Route::group(['prefix' => 'meeting-attendances'], function () {
        Route::get('/org-user-list', [MeetingAttendanceController::class, 'getOrgUse']); // Get organization user list
        Route::get('/', [MeetingAttendanceController::class, 'index']); // Get all meeting attendances
        Route::get('/{id}', [MeetingAttendanceController::class, 'show']); // Get a specific attendance
        Route::post('/', [MeetingAttendanceController::class, 'store']); // Create a new attendance
        Route::put('/{id}', [MeetingAttendanceController::class, 'update']); // Update a specific attendance
        Route::delete('/{id}', [MeetingAttendanceController::class, 'destroy']); // Delete a specific attendance
    });

    //Meeting MeetingGuestAttendance 
    //group name: meeting-guest-attendances
    // Route::get('/get-meeting-guest-attendances', [MeetingGuestAttendanceController::class, 'index']);
    // Route::get('/get-meeting-guest-attendance/{id}', [MeetingGuestAttendanceController::class, 'show']);
    // Route::post('/create-meeting-guest-attendance', [MeetingGuestAttendanceController::class, 'store']);
    // Route::put('/update-meeting-guest-attendance/{id}', [MeetingGuestAttendanceController::class, 'update']);
    // Route::delete('/delete-meeting-guest-attendance/{id}', [MeetingGuestAttendanceController::class, 'destroy']);
    Route::group(['prefix' => 'meeting-guest-attendances'], function () {
        Route::get('/', [MeetingGuestAttendanceController::class, 'index']); // Get all meeting guest attendances
        Route::get('/{id}', [MeetingGuestAttendanceController::class, 'show']); // Get a specific attendance
        Route::post('/', [MeetingGuestAttendanceController::class, 'store']); // Create a new attendance
        Route::put('/{id}', [MeetingGuestAttendanceController::class, 'update']); // Update a specific attendance
        Route::delete('/{id}', [MeetingGuestAttendanceController::class, 'destroy']); // Delete a specific attendance
    });
    
    //Event  
    //group name: events
    // Route::get('/get-events/{userId}', [EventController::class, 'index']);
    // Route::get('/get-event/{eventId}', [EventController::class, 'getEvent']);
    // Route::post('/create-event', [EventController::class, 'createEvent']);
    // Route::put('/update-event/{eventId}', [EventController::class, 'updateEvent']);
    // Route::delete('/delete-event/{eventId}', [EventController::class, 'deleteEvent']);

    Route::group(['prefix' => 'events'], function () {
        Route::get('/event/{eventId}', [EventController::class, 'getEvent']); // Get specific event
        Route::get('/', [EventController::class, 'index']); // Get events for a user
        Route::post('/', [EventController::class, 'store']); // Create a new event
        Route::put('/{eventId}', [EventController::class, 'update']); // Update a specific event
        Route::delete('/{eventId}', [EventController::class, 'destroy']); // Delete a specific event
    });


    // Event Attendance
    //group name: event-attendances
    // Route::get('/get-org-user-list', [EventAttendanceController::class, 'getOrgUse']);
    // Route::get('/get-event-attendances', [EventAttendanceController::class, 'index']);
    // Route::get('/get-event-attendance/{id}', [EventAttendanceController::class, 'show']);
    // Route::post('/create-event-attendance', [EventAttendanceController::class, 'store']);
    // Route::put('/update-event-attendance/{id}', [EventAttendanceController::class, 'update']);
    // Route::delete('/delete-event-attendance/{id}', [EventAttendanceController::class, 'destroy']);

    Route::group(['prefix' => 'event-attendances'], function () {
        Route::get('/get-org-user-list', [EventAttendanceController::class, 'getOrgUse']); // Get org user list
        Route::get('/', [EventAttendanceController::class, 'index']); // Get all event attendances
        Route::get('/{id}', [EventAttendanceController::class, 'show']); // Get specific event attendance
        Route::post('/', [EventAttendanceController::class, 'store']); // Create new event attendance
        Route::put('/{id}', [EventAttendanceController::class, 'update']); // Update event attendance
        Route::delete('/{id}', [EventAttendanceController::class, 'destroy']); // Delete event attendance
    });
    

    //Event Guest Attendance 
    //group name: event-guest-attendances
    // Route::get('/get-event-guest-attendances', [EventGuestAttendanceController::class, 'index']);
    // Route::get('/get-event-guest-attendance/{id}', [EventGuestAttendanceController::class, 'show']);
    // Route::post('/create-event-guest-attendance', [EventGuestAttendanceController::class, 'store']);
    // Route::put('/update-event-guest-attendance/{id}', [EventGuestAttendanceController::class, 'update']);
    // Route::delete('/delete-event-guest-attendance/{id}', [EventGuestAttendanceController::class, 'destroy']);

    Route::group(['prefix' => 'event-guest-attendances'], function () {
        Route::get('/', [EventGuestAttendanceController::class, 'index']); // Get all event guest attendances
        Route::get('/{id}', [EventGuestAttendanceController::class, 'show']); // Get a specific event guest attendance
        Route::post('/', [EventGuestAttendanceController::class, 'store']); // Create a new event guest attendance
        Route::put('/{id}', [EventGuestAttendanceController::class, 'update']); // Update an existing event guest attendance
        Route::delete('/{id}', [EventGuestAttendanceController::class, 'destroy']); // Delete an event guest attendance
    });
    
    // event-summary
    //group name: event-summaries
    // Route::get('/get-event-summary', [EventSummaryController::class, 'index']);
    // Route::get('/get-event-summary/{id}', [EventSummaryController::class, 'show']);
    // Route::post('/create-event-summary', [EventSummaryController::class, 'store']);
    // Route::put('/update-event-summary/{id}', [EventSummaryController::class, 'update']);
    // Route::delete('/delete-event-summary/{id}', [EventSummaryController::class, 'destroy']);

    Route::group(['prefix' => 'event-summaries'], function () {
        Route::get('/', [EventSummaryController::class, 'index']); // Get all event summaries
        Route::get('/{id}', [EventSummaryController::class, 'show']); // Get a specific event summary
        Route::post('/', [EventSummaryController::class, 'store']); // Create a new event summary
        Route::put('/{id}', [EventSummaryController::class, 'update']); // Update an existing event summary
        Route::delete('/{id}', [EventSummaryController::class, 'destroy']); // Delete an event summary
    });

    //Project
    //group name: projects
    // Route::get('org-project-list/{userId}', [ProjectController::class, 'index']);
    // Route::get('/get-project/{projectId}', [ProjectController::class, 'show']);
    // Route::post('create-project', [ProjectController::class, 'store']);
    // Route::put('update-project/{userId}', [ProjectController::class, 'update']);
    // Route::delete('/delete-project/{id}', [ProjectController::class, 'destroy']);

    Route::group(['prefix' => 'projects'], function () {
        Route::get('/', [ProjectController::class, 'index']); // Get organization-specific projects
        Route::get('/{projectId}', [ProjectController::class, 'show']); // Get specific project details
        Route::post('/', [ProjectController::class, 'store']); // Create new project
        Route::put('/{userId}', [ProjectController::class, 'update']); // Update project
        Route::delete('/{id}', [ProjectController::class, 'destroy']); // Delete project
    });
    

    //ProjectAttendance
    //group name: project-attendances
    // Route::get('/get-org-user-list', [ProjectAttendanceController::class, 'getOrgUse']);
    // Route::get('/get-project-attendances', [ProjectAttendanceController::class, 'index']);
    // Route::get('/get-project-attendance/{id}', [ProjectAttendanceController::class, 'show']);
    // Route::post('/create-project-attendance', [ProjectAttendanceController::class, 'store']);
    // Route::put('/update-project-attendance/{id}', [ProjectAttendanceController::class, 'update']);
    // Route::delete('/delete-project-attendance/{id}', [ProjectAttendanceController::class, 'destroy']);

    Route::group(['prefix' => 'project-attendances'], function () {
        Route::get('/org-user-list', [ProjectAttendanceController::class, 'getOrgUse']); // Get organization user list
        Route::get('/', [ProjectAttendanceController::class, 'index']); // Get all project attendances
        Route::get('/{id}', [ProjectAttendanceController::class, 'show']); // Get specific project attendance
        Route::post('/', [ProjectAttendanceController::class, 'store']); // Create new project attendance
        Route::put('/{id}', [ProjectAttendanceController::class, 'update']); // Update project attendance
        Route::delete('/{id}', [ProjectAttendanceController::class, 'destroy']); // Delete project attendance
    });
    

    //project Guest Attendance 
    //group name: project-guest-attendances
    // Route::get('/get-project-guest-attendances', [ProjectGuestAttendanceController::class, 'index']);
    // Route::get('/get-project-guest-attendance/{id}', [ProjectGuestAttendanceController::class, 'show']);
    // Route::post('/create-project-guest-attendance', [ProjectGuestAttendanceController::class, 'store']);
    // Route::put('/update-project-guest-attendance/{id}', [ProjectGuestAttendanceController::class, 'update']);
    // Route::delete('/delete-project-guest-attendance/{id}', [ProjectGuestAttendanceController::class, 'destroy']);

    Route::group(['prefix' => 'project-guest-attendances'], function () {
        Route::get('/', [ProjectGuestAttendanceController::class, 'index']); // Get all guest attendances
        Route::get('/{id}', [ProjectGuestAttendanceController::class, 'show']); // Get specific guest attendance
        Route::post('/', [ProjectGuestAttendanceController::class, 'store']); // Create new guest attendance
        Route::put('/{id}', [ProjectGuestAttendanceController::class, 'update']); // Update guest attendance
        Route::delete('/{id}', [ProjectGuestAttendanceController::class, 'destroy']); // Delete guest attendance
    });
    

    //Project Summery
    //group name: project-summaries
    // Route::get('/get-project-summary', [ProjectSummaryController::class, 'index']);
    // Route::get('/get-project-summary/{id}', [ProjectSummaryController::class, 'show']);
    // Route::post('/create-project-summary', [ProjectSummaryController::class, 'store']);
    // Route::put('/update-project-summary/{id}', [ProjectSummaryController::class, 'update']);
    // Route::delete('/delete-project-summary/{id}', [ProjectSummaryController::class, 'destroy']);

    Route::group(['prefix' => 'project-summaries'], function () {
        Route::get('/', [ProjectSummaryController::class, 'index']); // Get all project summaries
        Route::get('/{id}', [ProjectSummaryController::class, 'show']); // Get specific project summary
        Route::post('/', [ProjectSummaryController::class, 'store']); // Create a new project summary
        Route::put('/{id}', [ProjectSummaryController::class, 'update']); // Update project summary
        Route::delete('/{id}', [ProjectSummaryController::class, 'destroy']); // Delete project summary
    });
    

    // Founder
    //group name: founders
    // Route::post('create-founder', [FounderController::class, 'store']);
    // Route::get('get-founder/{userId}', [FounderController::class, 'index']);
    // Route::put('update-founder/{id}', [FounderController::class, 'update']);
    Route::group(['prefix' => 'founders'], function () {
        Route::get('/', [FounderController::class, 'index']); // Get founder by user ID
        Route::post('/', [FounderController::class, 'store']); // Create a new founder
        Route::put('/{id}', [FounderController::class, 'update']); // Update founder
    });
    

    // Asset
    //group name: assets
    // Route::get('/get-assets/{userId}', [AssetController::class, 'getAsset']);
    // Route::get('/get-asset/{assetId}', [AssetController::class, 'getAssetDetails']);
    // Route::post('/create-asset', [AssetController::class, 'store']);
    // Route::post('/update-asset/{id}', [AssetController::class, 'update']);
    // Route::delete('/delete-asset/{id}', [AssetController::class, 'destroy']);

    Route::group(['prefix' => 'assets'], function () {
        Route::get('/{assetId}', [AssetController::class, 'getAssetDetails']); // Allready show use //Get details of a specific asset
        Route::get('/', [AssetController::class, 'index']); // Get assets by user ID
        Route::post('/', [AssetController::class, 'store']); // Create new asset
        Route::put('/{id}', [AssetController::class, 'update']); // Update asset
        Route::delete('/{id}', [AssetController::class, 'destroy']); // Delete asset
    });
    

    // privacy_setups
    //group name: privacy-setups
    // Route::get('privacy-setups', [PrivacySetupController::class, 'index']);
    // Route::get('/get-all-privacy-setups', [PrivacySetupController::class, 'getAllPrivacySetupForSuperAdmin']);
    // Route::get('/get-privacy-setups', [PrivacySetupController::class, 'index']);
    // Route::post('/create-privacy-setup', [PrivacySetupController::class, 'store']);
    // Route::put('/update-privacy-setup/{id}', [PrivacySetupController::class, 'update']);
    // Route::delete('/delete-privacy-setup/{id}', [PrivacySetupController::class, 'destroy']);

    Route::group(['prefix' => 'privacy-setups'], function () {
        Route::get('/', [PrivacySetupController::class, 'index']); // Get all privacy setups
        Route::post('/', [PrivacySetupController::class, 'store']); // Create privacy setup
        Route::put('{id}', [PrivacySetupController::class, 'update']); // Update privacy setup
        Route::delete('{id}', [PrivacySetupController::class, 'destroy']); // Delete privacy setup
        Route::get('/all', [PrivacySetupController::class, 'getAllPrivacySetupForSuperAdmin']); // Get all privacy setups for super admin
    });
    

    //group name: asset-lifecycle-setups
    Route::get('asset-lifecycle-setups', [AssetLifecycleStatusController::class, 'index']);

    //Billing
    //group name: management-subscriptions
    // Route::get('management-subscriptions', [ManagementSubscriptionController::class, 'show']);
    // Route::post('management-subscriptions', [ManagementSubscriptionController::class, 'store']);
    // Route::put('management-subscriptions/{id}', [ManagementSubscriptionController::class, 'update']);
    // Route::delete('management-subscriptions{id}', [ManagementSubscriptionController::class, 'destroy']);

    Route::group(['prefix' => 'management-subscriptions'], function () {
        Route::get('/', [ManagementSubscriptionController::class, 'show']); // Show subscriptions
        Route::post('/', [ManagementSubscriptionController::class, 'store']); // Create subscription
        Route::put('{id}', [ManagementSubscriptionController::class, 'update']); // Update subscription
        Route::delete('{id}', [ManagementSubscriptionController::class, 'destroy']); // Delete subscription
    });
    

    //group name: no group (api name: management-pricings) only GET
    Route::get('/management-pricings', [ManagementPricingController::class, 'getUserPriceRate']);


    // ------------------- Individual----------------------------------------------------------------

    //group: individual-profile-image (only get and put)
    Route::get('/individual_profile_data/{userId}', [IndividualController::class, 'getProfileImage']);
    Route::get('/profileimage/{userId}', [IndividualController::class, 'getProfileImage']);
    Route::post('/profileimage/{userId}', [IndividualController::class, 'updateProfileImage']);

    //connected organisation list for individual user (no group)
    Route::get('/connected-org-list/{userId}', [IndividualController::class, 'getOrganisationByIndividualId']);

    //to get all individual user list (no group)
    Route::get('/individual-users', [IndividualController::class, 'getIndividualUser']);


    // ------------------- Superadmin----------------------------------------------------------------
    //API for Superadmin
    //group name: no group
    Route::get('/super_admin_user_data/{id}', [SuperAdminController::class, 'show']);


    //Finance
    //group name: management-and-storage-billings
    // Route::get('billing-list', [ManagementAndStorageBillingController::class, 'index']);
    // Route::get('superadmin-billing-list', [ManagementAndStorageBillingController::class, 'indexSuperAdmin']);
    // Route::get('get-billing/{id}', [ManagementAndStorageBillingController::class, 'show']); 
    // Route::post('create-billing', [ManagementAndStorageBillingController::class, 'store']);
    // Route::post('system-create-billing', [ManagementAndStorageBillingController::class, 'storeBySystem']);
    // Route::put('update-billing/{id}', [ManagementAndStorageBillingController::class, 'update']);
    // Route::delete('delete-billing/{id}', [ManagementAndStorageBillingController::class, 'destroy']);

    Route::group(['prefix' => 'management-and-storage-billings'], function () {
        Route::get('/', [ManagementAndStorageBillingController::class, 'index']); // Get list of billings
        Route::get('{id}', [ManagementAndStorageBillingController::class, 'show']); // Get single billing 
        Route::post('/', [ManagementAndStorageBillingController::class, 'store']); // Create new billing
        Route::put('{id}', [ManagementAndStorageBillingController::class, 'update']); // Update billing
        Route::delete('{id}', [ManagementAndStorageBillingController::class, 'destroy']); // Delete billing

        Route::get('/superadmin', [ManagementAndStorageBillingController::class, 'indexSuperAdmin']); // Get superadmin billing list
        Route::post('/system', [ManagementAndStorageBillingController::class, 'storeBySystem']); // Create billing by system
    });
    
    // Everyday billing
    //group name: every-day-member-count-and-billings
    // Route::get('every-day-member-count-and-bill-list', [EverydayMemberCountAndBillingController::class, 'index']);
    // Route::get('get-every-day-member-count-and-bill/{id}', [EverydayMemberCountAndBillingController::class, 'show']);
    // Route::post('create-every-day-member-count-and-bill', [EverydayMemberCountAndBillingController::class, 'superAdminStore']);
    // Route::put('update-every-day-member-count-and-bill/{id}', [EverydayMemberCountAndBillingController::class, 'update']);
    // Route::delete('delete-every-day-member-count-and-bill/{id}', [EverydayMemberCountAndBillingController::class, 'destroy']);
    Route::group(['prefix' => 'every-day-member-count-and-billings'], function () {
        Route::get('/', [EverydayMemberCountAndBillingController::class, 'index']); // Get list of member count and billings
        Route::get('{id}', [EverydayMemberCountAndBillingController::class, 'show']); // Get single member count and billing
        Route::post('/', [EverydayMemberCountAndBillingController::class, 'superAdminStore']); // allready store use // Create new member count and billing
        Route::put('{id}', [EverydayMemberCountAndBillingController::class, 'update']); // Update member count and billing
        Route::delete('{id}', [EverydayMemberCountAndBillingController::class, 'destroy']); // Delete member count and billing
    });
    
    // Everyday storage billing
    //group name: every-day-storage-billings
    // Route::get('everyday-storage-billing-list', [EverydayStorageBillingController::class, 'index']);
    // Route::get('get-everyday-storage-billing/{id}', [EverydayStorageBillingController::class, 'show']);
    // Route::post('create-everyday-storage-billing', [EverydayStorageBillingController::class, 'superAdminStore']);
    // Route::put('update-everyday-storage-billing/{id}', [EverydayStorageBillingController::class, 'update']);
    // Route::delete('delete-everyday-storage-billing/{id}', [EverydayStorageBillingController::class, 'destroy']);

    Route::group(['prefix' => 'every-day-storage-billings'], function () {
        Route::get('/', [EverydayStorageBillingController::class, 'index']); // Get list of storage billings
        Route::get('{id}', [EverydayStorageBillingController::class, 'show']); // Get single storage billing
        Route::post('/', [EverydayStorageBillingController::class, 'superAdminStore']); // Create new storage billing
        Route::put('{id}', [EverydayStorageBillingController::class, 'update']); // Update storage billing
        Route::delete('{id}', [EverydayStorageBillingController::class, 'destroy']); // Delete storage billing
    });

    // Invoice
    //group name: invoices
    // Route::get('invoices', [InvoiceController::class, 'index']);
    // Route::get('all-invoices', [InvoiceController::class, 'indexForSuperadmin']);
    // Route::get('get-invoice/{id}', [InvoiceController::class, 'show']);
    // Route::post('create-invoice', [InvoiceController::class, 'store']);
    // Route::put('update-invoice/{id}', [InvoiceController::class, 'update']);
    // Route::delete('delete-invoice/{id}', [InvoiceController::class, 'destroy']);

    Route::group(['prefix' => 'invoices'], function () {
        Route::get('/', [InvoiceController::class, 'index']); // Default route for invoices
        Route::get('{id}', [InvoiceController::class, 'show']); // Get single invoice
        Route::post('/', [InvoiceController::class, 'store']); // Create new invoice
        Route::put('{id}', [InvoiceController::class, 'update']); // Update invoice
        Route::delete('{id}', [InvoiceController::class, 'destroy']); // Delete invoice
        Route::get('/all', [InvoiceController::class, 'indexForSuperadmin']); // Superadmin specific invoices
    });
    

    // ManagementPricing
    //group name: management-pricings
    // Route::get('price-rate', [ManagementPricingController::class, 'index']);
    // Route::put('price-rate/update', [ManagementPricingController::class, 'update']);
    // Route::get('/all-user-price-rate', [ManagementPricingController::class, 'getAllUserPriceRate']);
    Route::group(['prefix' => 'management-pricings'], function () {
        Route::get('/', [ManagementPricingController::class, 'index']); // Default route
        // Route::post('/', [ManagementPricingController::class, 'store']); // If needed for creating a price rate
        // Route::put('{id}', [ManagementPricingController::class, 'update']); // General update
        // Route::delete('{id}', [ManagementPricingController::class, 'destroy']); // General delete
        Route::put('/update', [ManagementPricingController::class, 'update']);
        Route::get('/all-user-price-rate', [ManagementPricingController::class, 'getAllUserPriceRate']);
    });

    //Currency
    //group name: currencies
    // Route::get('currencies', [CurrencyController::class, 'index']);
    // Route::post('currencies', [CurrencyController::class, 'store']);
    // Route::put('currencies/{id}', [CurrencyController::class, 'update']);
    // Route::delete('currencies/{id}', [CurrencyController::class, 'destroy']);
    Route::group(['prefix' => 'currencies'], function () {
        Route::get('/', [CurrencyController::class, 'index']);
        Route::post('/', [CurrencyController::class, 'store']);
        Route::put('{id}', [CurrencyController::class, 'update']);
        Route::delete('{id}', [CurrencyController::class, 'destroy']);
    });

    // RegionalTaxRateController
    //group name: regional-tax-rates
    // Route::get('/get-region-tax-rates', [RegionalTaxRateController::class, 'index']);
    // Route::get('/get-region-tax-rate/{id}', [RegionalTaxRateController::class, 'show']);
    // Route::post('/create-region-tax-rate', [RegionalTaxRateController::class, 'store']);
    // Route::put('/update-region-tax-rate/{id}', [RegionalTaxRateController::class, 'update']);
    // Route::delete('/delete-region-tax-rate/{id}', [RegionalTaxRateController::class, 'destroy']);
    Route::group(['prefix' => 'regional-tax-rates'], function () {
        Route::get('/', [RegionalTaxRateController::class, 'index']);
        Route::get('{id}', [RegionalTaxRateController::class, 'show']);
        Route::post('/', [RegionalTaxRateController::class, 'store']);
        Route::put('{id}', [RegionalTaxRateController::class, 'update']);
        Route::delete('{id}', [RegionalTaxRateController::class, 'destroy']);
    });

    // Country
    //group name: countries
    // Route::get('/get-countries', [CountryController::class, 'index']);
    // Route::post('/create-country', [CountryController::class, 'store']);
    // Route::put('/update-country/{id}', [CountryController::class, 'update']);
    // Route::delete('/delete-country/{id}', [CountryController::class, 'destroy']);
    Route::group(['prefix' => 'countries'], function () {
        Route::get('/', [CountryController::class, 'index']);
        Route::post('/', [CountryController::class, 'store']);
        Route::put('{id}', [CountryController::class, 'update']);
        Route::delete('{id}', [CountryController::class, 'destroy']);
    });
    

    // dialing-code
    //group name: dialing-codes
    // Route::get('/get-dialing-codes', [DialingCodeController::class, 'index']);
    // Route::post('/create-dialing-code', [DialingCodeController::class, 'store']);
    // Route::put('/update-dialing-code/{id}', [DialingCodeController::class, 'update']);
    // Route::delete('/delete-dialing-code/{id}', [DialingCodeController::class, 'destroy']);

    Route::group(['prefix' => 'dialing-codes'], function () {
        Route::get('/', [DialingCodeController::class, 'index']); // Get all dialing codes
        Route::post('/', [DialingCodeController::class, 'store']); // Create a new dialing code
        Route::put('/{id}', [DialingCodeController::class, 'update']); // Update a specific dialing code
        Route::delete('/{id}', [DialingCodeController::class, 'destroy']); // Delete a specific dialing code
    });
    

    // ConductTypeController
    //group name: conduct-types
    // Route::get('/get-conduct-types', [ConductTypeController::class, 'index']);
    // Route::post('/create-conduct-type', [ConductTypeController::class, 'store']);
    // Route::put('/update-conduct-type/{id}', [ConductTypeController::class, 'update']);
    // Route::delete('/delete-conduct-type/{id}', [ConductTypeController::class, 'destroy']);

    Route::group(['prefix' => 'conduct-types'], function () {
        Route::get('/', [ConductTypeController::class, 'index']); // Get all conduct types
        Route::post('/', [ConductTypeController::class, 'store']); // Create a new conduct type
        Route::put('/{id}', [ConductTypeController::class, 'update']); // Update a specific conduct type
        Route::delete('/{id}', [ConductTypeController::class, 'destroy']); // Delete a specific conduct type
    });
    
    // AttendanceTypeController
    //group name: attendance-types
    // Route::get('/get-attendance-types', [AttendanceTypeController::class, 'index']);
    // Route::post('/create-attendance-type', [AttendanceTypeController::class, 'store']);
    // Route::put('/update-attendance-type/{id}', [AttendanceTypeController::class, 'update']);
    // Route::delete('/delete-attendance-type/{id}', [AttendanceTypeController::class, 'destroy']);

    Route::group(['prefix' => 'attendance-types'], function () {
        Route::get('/', [AttendanceTypeController::class, 'index']); // Get all attendance types
        Route::post('/', [AttendanceTypeController::class, 'store']); // Create a new attendance type
        Route::put('/{id}', [AttendanceTypeController::class, 'update']); // Update a specific attendance type
        Route::delete('/{id}', [AttendanceTypeController::class, 'destroy']); // Delete a specific attendance type
    });
    

    // membership-type
    //group name: membership-types
    // Route::get('/get-membership-types', [MembershipTypeController::class, 'index']);
    // Route::post('/create-membership-type', [MembershipTypeController::class, 'store']);
    // Route::put('/update-membership-type/{id}', [MembershipTypeController::class, 'update']);
    // Route::delete('/delete-membership-type/{id}', [MembershipTypeController::class, 'destroy']);

    Route::group(['prefix' => 'membership-types'], function () {
        Route::get('/', [MembershipTypeController::class, 'index']); // Get all membership types
        Route::post('/', [MembershipTypeController::class, 'store']); // Create a new membership type
        Route::put('/{id}', [MembershipTypeController::class, 'update']); // Update a specific membership type
        Route::delete('/{id}', [MembershipTypeController::class, 'destroy']); // Delete a specific membership type
    });
    
    // designation
    //group name: designations
    // Route::get('/get-designations', [DesignationController::class, 'index']);
    // Route::post('/create-designation', [DesignationController::class, 'store']);
    // Route::put('/update-designation/{id}', [DesignationController::class, 'update']);
    // Route::delete('/delete-designation/{id}', [DesignationController::class, 'destroy']);

    Route::group(['prefix' => 'designations'], function () {
        Route::get('/', [DesignationController::class, 'index']); // Get all designations
        Route::post('/', [DesignationController::class, 'store']); // Create a new designation
        Route::put('/{id}', [DesignationController::class, 'update']); // Update a specific designation
        Route::delete('/{id}', [DesignationController::class, 'destroy']); // Delete a specific designation
    });
    
    // Languages
    //group name: languages
    // Route::get('/get-languages', [LanguageListController::class, 'index']);
    // Route::post('/create-language', [LanguageListController::class, 'store']);
    // Route::put('/update-language/{id}', [LanguageListController::class, 'update']);
    // Route::delete('/delete-language/{id}', [LanguageListController::class, 'destroy']);

    Route::group(['prefix' => 'languages'], function () {
        Route::get('/', [LanguageListController::class, 'index']); // Get all languages
        Route::post('/', [LanguageListController::class, 'store']); // Create a new language
        Route::put('/{id}', [LanguageListController::class, 'update']); // Update a specific language
        Route::delete('/{id}', [LanguageListController::class, 'destroy']); // Delete a specific language
    });
    
    // time-zone-setup
    //group name: time-zone-setups
    // Route::get('/get-time-zone-setups', [TimeZoneSetupController::class, 'index']);
    // Route::post('/create-time-zone-setup', [TimeZoneSetupController::class, 'store']);
    // Route::put('/update-time-zone-setup/{id}', [TimeZoneSetupController::class, 'update']);
    // Route::delete('/delete-time-zone-setup/{id}', [TimeZoneSetupController::class, 'destroy']);

    Route::group(['prefix' => 'time-zone-setups'], function () {
        Route::get('/', [TimeZoneSetupController::class, 'index']); // Get all time zone setups
        Route::post('/', [TimeZoneSetupController::class, 'store']); // Create a new time zone setup
        Route::put('/{id}', [TimeZoneSetupController::class, 'update']); // Update a specific time zone setup
        Route::delete('/{id}', [TimeZoneSetupController::class, 'destroy']); // Delete a specific time zone setup
    });
    
    // User countries
    //group name: user-countries
    // Route::get('/get-user-list', [UserCountryController::class, 'getUser']);
    // Route::get('/get-user-countries', [UserCountryController::class, 'index']);
    // Route::post('/create-user-country', [UserCountryController::class, 'store']);
    // Route::put('/update-user-country/{id}', [UserCountryController::class, 'update']);
    // Route::delete('/delete-user-country/{id}', [UserCountryController::class, 'destroy']);

    Route::get('/get-user-list', [UserCountryController::class, 'getUser']);
    Route::group(['prefix' => 'user-countries'], function () {
        Route::get('/', [UserCountryController::class, 'index']); // Get all user countries
        Route::get('/{id}', [UserCountryController::class, 'show']); // Get a specific user country by ID
        Route::post('/', [UserCountryController::class, 'store']); // Create a new user country
        Route::put('/{id}', [UserCountryController::class, 'update']); // Update a specific user country
        Route::delete('/{id}', [UserCountryController::class, 'destroy']); // Delete a specific user country
    });
    
    // RegionController
    //group name: regions
    // Route::get('/get-regions', [RegionController::class, 'index']);
    // Route::get('/get-region/{id}', [RegionController::class, 'show']);
    // Route::post('/create-region', [RegionController::class, 'store']);
    // Route::put('/update-region/{id}', [RegionController::class, 'update']);
    // Route::delete('/delete-region/{id}', [RegionController::class, 'destroy']);

    Route::group(['prefix' => 'regions'], function () {
        Route::get('/', [RegionController::class, 'index']); // Get all regions
        Route::get('/{id}', [RegionController::class, 'show']); // Get a specific region by ID
        Route::post('/', [RegionController::class, 'store']); // Create a new region
        Route::put('/{id}', [RegionController::class, 'update']); // Update a specific region
        Route::delete('/{id}', [RegionController::class, 'destroy']); // Delete a specific region
    });
    
    // CountryRegionController
    //group name: country-regions
    // Route::get('/get-country-regions', [CountryRegionController::class, 'index']);
    // Route::get('/get-country-region/{id}', [CountryRegionController::class, 'show']);
    // Route::post('/create-country-region', [CountryRegionController::class, 'store']);
    // Route::put('/update-country-region/{id}', [CountryRegionController::class, 'update']);
    // Route::delete('/delete-country-region/{id}', [CountryRegionController::class, 'destroy']);

    Route::group(['prefix' => 'country-regions'], function () {
        Route::get('/', [CountryRegionController::class, 'index']); // Get all country regions
        Route::get('/{id}', [CountryRegionController::class, 'show']); // Get a specific country region by ID
        Route::post('/', [CountryRegionController::class, 'store']); // Create a new country region
        Route::put('/{id}', [CountryRegionController::class, 'update']); // Update a specific country region
        Route::delete('/{id}', [CountryRegionController::class, 'destroy']); // Delete a specific country region
    });
    
    // RegionCurrencyController
    //group name: region-currencies
    // Route::get('/get-region-currencies', [RegionCurrencyController::class, 'index']);
    // Route::get('/get-region-currency/{id}', [RegionCurrencyController::class, 'show']);
    // Route::post('/create-region-currency', [RegionCurrencyController::class, 'store']);
    // Route::put('/update-region-currency/{id}', [RegionCurrencyController::class, 'update']);
    // Route::delete('/delete-region-currency/{id}', [RegionCurrencyController::class, 'destroy']);

    Route::group(['prefix' => 'region-currencies'], function () {
        Route::get('/', [RegionCurrencyController::class, 'index']); // Get all region currencies
        Route::get('/{id}', [RegionCurrencyController::class, 'show']); // Get a specific region currency by ID
        Route::post('/', [RegionCurrencyController::class, 'store']); // Create a new region currency
        Route::put('/{id}', [RegionCurrencyController::class, 'update']); // Update a specific region currency
        Route::delete('/{id}', [RegionCurrencyController::class, 'destroy']); // Delete a specific region currency
    });
    
    //BusinessTypeController
    //group name: business-types
    // Route::get('get-business-types', [BusinessTypeController::class, 'index']);
    // Route::post('get-business-type/{id}', [BusinessTypeController::class, 'show']);
    // Route::post('create-business-type', [BusinessTypeController::class, 'store']);
    // Route::put('update-business-type/{id}', [BusinessTypeController::class, 'update']);
    // Route::delete('delete-business-type/{id}', [BusinessTypeController::class, 'destroy']);

    Route::group(['prefix' => 'business-types'], function () {
        Route::get('/', [BusinessTypeController::class, 'index']); // Get all business types
        Route::get('/{id}', [BusinessTypeController::class, 'show']); // Get a specific business type by ID
        Route::post('/', [BusinessTypeController::class, 'store']); // Create a new business type
        Route::put('/{id}', [BusinessTypeController::class, 'update']); // Update a specific business type
        Route::delete('/{id}', [BusinessTypeController::class, 'destroy']); // Delete a specific business type
    });
    
    // CategoryController
    //group name: categories
    // Route::get('get-categories', [CategoryController::class, 'index']);
    // Route::get('get-category/{id}', [CategoryController::class, 'show']);
    // Route::post('create-category', [CategoryController::class, 'store']);
    // Route::put('update-category/{id}', [CategoryController::class, 'update']);
    // Route::delete('delete-category/{id}', [CategoryController::class, 'destroy']);

    Route::group(['prefix' => 'categories'], function () {
        Route::get('/', [CategoryController::class, 'index']); // Get all categories
        Route::get('/{id}', [CategoryController::class, 'show']); // Get a specific category by ID
        Route::post('/', [CategoryController::class, 'store']); // Create a new category
        Route::put('/{id}', [CategoryController::class, 'update']); // Update a specific category
        Route::delete('/{id}', [CategoryController::class, 'destroy']); // Delete a specific category
    });
    

    // SubCategoryController
    //group name: sub-categories
    // Route::get('get-sub-categories', [SubCategoryController::class, 'index']);
    // Route::get('get-sub-category/{id}', [SubCategoryController::class, 'show']);
    // Route::post('create-sub-category', [SubCategoryController::class, 'store']);
    // Route::put('update-sub-category/{id}', [SubCategoryController::class, 'update']);
    // Route::delete('delete-sub-category/{id}', [SubCategoryController::class, 'destroy']);
    
    Route::group(['prefix' => 'sub-categories'], function () {
        Route::get('/', [SubCategoryController::class, 'index']); // Get all sub-categories
        Route::get('/{id}', [SubCategoryController::class, 'show']); // Get a specific sub-category by ID
        Route::post('/', [SubCategoryController::class, 'store']); // Create a new sub-category
        Route::put('/{id}', [SubCategoryController::class, 'update']); // Update a specific sub-category
        Route::delete('/{id}', [SubCategoryController::class, 'destroy']); // Delete a specific sub-category
    });
    
    // SubSubCategoryController
    //group name: sub-sub-categories
    // Route::get('get-sub-sub-categories', [SubSubCategoryController::class, 'index']);
    // Route::get('get-sub-sub-category/{id}', [SubSubCategoryController::class, 'show']);
    // Route::post('create-sub-sub-category', [SubSubCategoryController::class, 'store']);
    // Route::put('update-sub-sub-category/{id}', [SubSubCategoryController::class, 'update']);
    // Route::delete('delete-sub-sub-category/{id}', [SubSubCategoryController::class, 'destroy']);

    Route::group(['prefix' => 'sub-sub-categories'], function () {
        Route::get('/', [SubSubCategoryController::class, 'index']); // Get all sub-sub-categories
        Route::get('/{id}', [SubSubCategoryController::class, 'show']); // Get a specific sub-sub-category by ID
        Route::post('/', [SubSubCategoryController::class, 'store']); // Create a new sub-sub-category
        Route::put('/{id}', [SubSubCategoryController::class, 'update']); // Update a specific sub-sub-category
        Route::delete('/{id}', [SubSubCategoryController::class, 'destroy']); // Delete a specific sub-sub-category
    });
    
    // BrandController
    //group name: brands
    // Route::get('get-brands', [BrandController::class, 'index']);
    // Route::get('get-brand/{id}', [BrandController::class, 'show']);
    // Route::post('create-brand', [BrandController::class, 'store']);
    // Route::put('update-brand/{id}', [BrandController::class, 'update']);
    // Route::delete('delete-brand/{id}', [BrandController::class, 'destroy']);

    Route::group(['prefix' => 'brands'], function () {
        Route::get('/', [BrandController::class, 'index']); // Get all brands
        Route::get('/{id}', [BrandController::class, 'show']); // Get a specific brand by ID
        Route::post('/', [BrandController::class, 'store']); // Create a new brand
        Route::put('/{id}', [BrandController::class, 'update']); // Update a specific brand
        Route::delete('/{id}', [BrandController::class, 'destroy']); // Delete a specific brand
    });
    
    // ProductController
    //group name: products
    // Route::get('get-products', [ProductController::class, 'index']);
    // Route::get('get-product/{id}', [ProductController::class, 'show']);
    // Route::post('create-product', [ProductController::class, 'store']);
    // Route::put('update-product/{id}', [ProductController::class, 'update']);
    // Route::delete('delete-product/{id}', [ProductController::class, 'destroy']);

    Route::group(['prefix' => 'products'], function () {
        Route::get('/', [ProductController::class, 'index']); // Get all products
        Route::get('/{id}', [ProductController::class, 'show']); // Get a specific product by ID
        Route::post('/', [ProductController::class, 'store']); // Create a new product
        Route::put('/{id}', [ProductController::class, 'update']); // Update a specific product
        Route::delete('/{id}', [ProductController::class, 'destroy']); // Delete a specific product
    });
    
    //...................... E-commerce...............................................
    // OrderController
    //group name: orders
    // Route::get('get-orders', [OrderController::class, 'index']);
    // Route::get('get-order/{id}', [OrderController::class, 'show']);
    // Route::post('create-order', [OrderController::class, 'store']);
    // Route::put('update-order/{id}', [OrderController::class, 'update']);
    // Route::delete('delete-order/{id}', [OrderController::class, 'destroy']);

    Route::group(['prefix' => 'orders'], function () {
        Route::get('/', [OrderController::class, 'index']); // Get all orders
        Route::get('/{id}', [OrderController::class, 'show']); // Get a specific order by ID
        Route::post('/', [OrderController::class, 'store']); // Create a new order
        Route::put('/{id}', [OrderController::class, 'update']); // Update a specific order
        Route::delete('/{id}', [OrderController::class, 'destroy']); // Delete a specific order
    });
    
    // OrderItemController
    //group name: order-items
    // Route::get('get-order-items', [OrderItemController::class, 'index']);
    // Route::get('get-order-item/{id}', [OrderItemController::class, 'show']);
    // Route::post('create-order-item', [OrderItemController::class, 'store']);
    // Route::put('update-order-item/{id}', [OrderItemController::class, 'update']);
    // Route::delete('delete-order-item/{id}', [OrderItemController::class, 'destroy']);

    // not use
    Route::group(['prefix' => 'order-items'], function () {
        Route::get('/', [OrderItemController::class, 'index']); // Get all order items
        Route::get('/{id}', [OrderItemController::class, 'show']); // Get a specific order item by ID
        Route::post('/', [OrderItemController::class, 'store']); // Create a new order item
        Route::put('/{id}', [OrderItemController::class, 'update']); // Update a specific order item
        Route::delete('/{id}', [OrderItemController::class, 'destroy']); // Delete a specific order item
    });
    
    //OrderDetailController
    //group name: order-details
    // Route::get('get-order-details', [OrderDetailController::class, 'index']);
    // Route::get('get-order-detail/{id}', [OrderDetailController::class, 'show']);
    // Route::post('create-order-detail', [OrderDetailController::class, 'store']);
    // Route::put('update-order-detail/{id}', [OrderDetailController::class, 'update']);
    // Route::delete('delete-order-detail/{id}', [OrderDetailController::class, 'destroy']);

    // not use
    Route::group(['prefix' => 'order-details'], function () {
        Route::get('/', [OrderDetailController::class, 'index']); // Get all order details
        Route::get('/{id}', [OrderDetailController::class, 'show']); // Get a specific order detail by ID
        Route::post('/', [OrderDetailController::class, 'store']); // Create a new order detail
        Route::put('/{id}', [OrderDetailController::class, 'update']); // Update a specific order detail
        Route::delete('/{id}', [OrderDetailController::class, 'destroy']); // Delete a specific order detail
    });
    
});
