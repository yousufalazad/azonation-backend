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
    Route::get('address/{userId}', [AddressController::class, 'show']);
    Route::post('address', [AddressController::class, 'store']);
    Route::put('address/{userId}', [AddressController::class, 'update']);

    //API for phone number
    //group name: phone-numbers
    Route::get('phone-number/{userId}', [PhoneNumberController::class, 'show']);
    Route::put('phone-number/{userId}', [PhoneNumberController::class, 'update']);
    Route::get('dialing-codes', [PhoneNumberController::class, 'getAllDialingCodes']);

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
    Route::get('/get-transactions', [AccountController::class, 'getTransactions']);
    Route::post('/create-transaction', [AccountController::class, 'createTransaction']);
    Route::put('/update-transaction/{id}', [AccountController::class, 'updateTransaction']);
    Route::delete('/delete-transaction/{id}', [AccountController::class, 'deleteTransaction']);

    // Funds
    //group name: funds
    Route::get('/get-funds', [AccountFundController::class, 'index']);
    Route::post('/create-fund', [AccountFundController::class, 'store']);
    Route::put('/update-fund/{id}', [AccountFundController::class, 'update']);
    Route::delete('/delete-fund/{id}', [AccountFundController::class, 'destroy']);

    // HistoryController
    //group name: histories
    Route::get('/get-org-histories', [HistoryController::class, 'index']);
    Route::get('/get-org-history/{id}', [HistoryController::class, 'show']);
    Route::post('/create-org-history', [HistoryController::class, 'store']);
    Route::put('/update-org-history/{id}', [HistoryController::class, 'update']);
    Route::delete('/delete-org-history/{id}', [HistoryController::class, 'destroy']);

    //Year plan
    //group name: year-plans
    Route::get('/get-year-plans', [YearPlanController::class, 'index']);
    Route::get('/year-plan/{id}', [YearPlanController::class, 'show']);
    Route::post('/create-year-plan', [YearPlanController::class, 'store']);
    Route::post('/update-year-plan/{id}', [YearPlanController::class, 'update']);
    Route::delete('/delete-year-plan/{id}', [YearPlanController::class, 'destroy']);

    // Recognition
    //group name: recognitions
    Route::get('/get-recognitions', [RecognitionController::class, 'index']);
    Route::get('/get-recognition/{id}', [RecognitionController::class, 'show']);
    Route::post('/create-recognition', [RecognitionController::class, 'store']);
    Route::put('/update-recognition/{id}', [RecognitionController::class, 'update']);
    Route::delete('/delete-recognition/{id}', [RecognitionController::class, 'destroy']);

    // StrategicPlan
    //group name: strategic-plans
    Route::get('/get-strategic-plans', [StrategicPlanController::class, 'index']);
    Route::get('/get-strategic-plan/{id}', [StrategicPlanController::class, 'show']);
    Route::post('/create-strategic-plan', [StrategicPlanController::class, 'store']);
    Route::put('/update-strategic-plan/{id}', [StrategicPlanController::class, 'update']);
    Route::delete('/delete-strategic-plan/{id}', [StrategicPlanController::class, 'destroy']);

    // SuccessStoryController
    //group name: success-stories
    Route::get('/get-records', [SuccessStoryController::class, 'index']);
    Route::get('/get-record/{id}', [SuccessStoryController::class, 'show']);
    Route::post('/create-record', [SuccessStoryController::class, 'store']);
    Route::put('/update-record/{id}', [SuccessStoryController::class, 'update']);
    Route::delete('/delete-record/{id}', [SuccessStoryController::class, 'destroy']);

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

    //Office record
    //group name: office-documents (controller change hoye OfficeDocumentController koiro + Model and DB change)
    Route::get('/get-office-records', [OfficeRecordController::class, 'index']);
    Route::get('/get-office-record/{recordId}', [OfficeRecordController::class, 'show']);
    Route::post('/create-office-record', [OfficeRecordController::class, 'store']);
    Route::put('/update-office-record/{id}', action: [OfficeRecordController::class, 'update']);
    Route::delete('/delete-office-record/{id}', [OfficeRecordController::class, 'destroy']);

    //API for org membership
    //group name: org-members
    Route::get('/org-members/{userId}', [OrgMemberController::class, 'getOrgMembers']);

    Route::post('/search_individual', [OrgMemberController::class, 'search']);
    Route::post('/add_member', [OrgMemberController::class, 'addMember']); //org-members er sathe conflict kore, org-members er sathe conflict kore but org-members (create) proper mone hocche
    Route::get('/org-member-list/{userId}', [OrgMemberController::class, 'getMemberList']); //org-members --> index
    Route::get('/org-all-members', [OrgMemberController::class, 'getOrgAllMembers']);

    //group name: no group
    Route::get('/org-all-member-name', [OrgMemberController::class, 'getOrgAllMemberName']);
    Route::get('/total-org-member-count/{userId}', [OrgMemberController::class, 'totalOrgMemberCount']);

    // OrgIndependentMemberController
    //group name: independent-members
    Route::get('get-independent-members', [OrgIndependentMemberController::class, 'index']);
    Route::get('get-independent-member/{id}', [OrgIndependentMemberController::class, 'show']);
    Route::post('create-independent-member', [OrgIndependentMemberController::class, 'store']);
    Route::put('update-independent-member/{id}', [OrgIndependentMemberController::class, 'update']);
    Route::delete('delete-independent-member/{id}', [OrgIndependentMemberController::class, 'destroy']);

    //for Org Administrator
    //group name: org-administrators
    Route::post('/search-individual', [OrgAdministratorController::class, 'search']); //create function e searching korabo, ekhane group name same thakbe
    Route::post('/add_administrator', [OrgAdministratorController::class, 'store']);
    Route::get('/org-administrator/{orgId}', [OrgAdministratorController::class, 'show']);
    Route::put('/update-administrator/{orgId}', [OrgAdministratorController::class, 'update']);

    //Committee
    //group name: committees
    Route::get('org-committee-list/{userId}', [CommitteeController::class, 'getCommitteeListByUserId']);
    Route::post('create_committee', [CommitteeController::class, 'store']);
    Route::put('update_committee/{id}', [CommitteeController::class, 'update']);
    Route::delete('org-committee/{id}', [CommitteeController::class, 'destroy']);

    //Committee Member
    //group name: committee-members
    Route::get('/get-committee-members', [CommitteeMemberController::class, 'index']);
    Route::get('/get-committee-member/{id}', [CommitteeMemberController::class, 'show']);
    Route::post('/create-committee-member', [CommitteeMemberController::class, 'store']);
    Route::put('/update-committee-member/{id}', [CommitteeMemberController::class, 'update']);
    Route::delete('/delete-committee-member/{id}', [CommitteeMemberController::class, 'destroy']);

    //Meeting
    //group name: meetings
    // Route::get('/get-meetings', [MeetingController::class, 'index']);
    Route::get('/get-meeting/{id}', [MeetingController::class, 'show']); // Fetch meeting details
    Route::get('/get-org-meetings', [MeetingController::class, 'getOrgMeeting']);
    Route::post('/create-meeting', [MeetingController::class, 'store']);
    Route::put('/update-meeting/{id}', [MeetingController::class, 'update']);
    Route::delete('/delete-meeting/{id}', [MeetingController::class, 'destroy']);

    // meeting-minutes
    //group name: meeting-minutes
    Route::get('/get-meeting-minutes', [MeetingMinutesController::class, 'index']);
    Route::get('/get-meeting-minutes/{id}', [MeetingMinutesController::class, 'show']);
    Route::post('/create-meeting-minutes', [MeetingMinutesController::class, 'store']);
    Route::post('/update-meeting-minutes/{id}', [MeetingMinutesController::class, 'update']);
    Route::delete('/delete-meeting-minutes/{id}', [MeetingMinutesController::class, 'destroy']);

    //Meeting MeetingAttendance
    //group name: meeting-attendances
    Route::get('/get-org-user-list', [MeetingAttendanceController::class, 'getOrgUse']);
    Route::get('/get-meeting-attendances', [MeetingAttendanceController::class, 'index']);
    Route::get('/get-meeting-attendance/{id}', [MeetingAttendanceController::class, 'show']);
    Route::post('/create-meeting-attendance', [MeetingAttendanceController::class, 'store']);
    Route::put('/update-meeting-attendance/{id}', [MeetingAttendanceController::class, 'update']);
    Route::delete('/delete-meeting-attendance/{id}', [MeetingAttendanceController::class, 'destroy']);

    //Meeting MeetingGuestAttendance 
    //group name: meeting-guest-attendances
    Route::get('/get-meeting-guest-attendances', [MeetingGuestAttendanceController::class, 'index']);
    Route::get('/get-meeting-guest-attendance/{id}', [MeetingGuestAttendanceController::class, 'show']);
    Route::post('/create-meeting-guest-attendance', [MeetingGuestAttendanceController::class, 'store']);
    Route::put('/update-meeting-guest-attendance/{id}', [MeetingGuestAttendanceController::class, 'update']);
    Route::delete('/delete-meeting-guest-attendance/{id}', [MeetingGuestAttendanceController::class, 'destroy']);

    //Event  
    //group name: events
    Route::get('/get-events/{userId}', [EventController::class, 'index']);
    Route::get('/get-event/{eventId}', [EventController::class, 'getEvent']);
    Route::post('/create-event', [EventController::class, 'createEvent']);
    Route::put('/update-event/{eventId}', [EventController::class, 'updateEvent']);
    Route::delete('/delete-event/{eventId}', [EventController::class, 'deleteEvent']);

    // Event Attendance
    //group name: event-attendances
    Route::get('/get-org-user-list', [EventAttendanceController::class, 'getOrgUse']);
    Route::get('/get-event-attendances', [EventAttendanceController::class, 'index']);
    Route::get('/get-event-attendance/{id}', [EventAttendanceController::class, 'show']);
    Route::post('/create-event-attendance', [EventAttendanceController::class, 'store']);
    Route::put('/update-event-attendance/{id}', [EventAttendanceController::class, 'update']);
    Route::delete('/delete-event-attendance/{id}', [EventAttendanceController::class, 'destroy']);

    //Event Guest Attendance 
    //group name: event-guest-attendances
    Route::get('/get-event-guest-attendances', [EventGuestAttendanceController::class, 'index']);
    Route::get('/get-event-guest-attendance/{id}', [EventGuestAttendanceController::class, 'show']);
    Route::post('/create-event-guest-attendance', [EventGuestAttendanceController::class, 'store']);
    Route::put('/update-event-guest-attendance/{id}', [EventGuestAttendanceController::class, 'update']);
    Route::delete('/delete-event-guest-attendance/{id}', [EventGuestAttendanceController::class, 'destroy']);

    // event-summary
    //group name: event-summaries
    Route::get('/get-event-summary', [EventSummaryController::class, 'index']);
    Route::get('/get-event-summary/{id}', [EventSummaryController::class, 'show']);
    Route::post('/create-event-summary', [EventSummaryController::class, 'store']);
    Route::put('/update-event-summary/{id}', [EventSummaryController::class, 'update']);
    Route::delete('/delete-event-summary/{id}', [EventSummaryController::class, 'destroy']);


    //Project
    //group name: projects
    Route::get('org-project-list/{userId}', [ProjectController::class, 'index']);
    Route::get('/get-project/{projectId}', [ProjectController::class, 'show']);
    Route::post('create-project', [ProjectController::class, 'store']);
    Route::put('update-project/{userId}', [ProjectController::class, 'update']);
    Route::delete('/delete-project/{id}', [ProjectController::class, 'destroy']);

    //ProjectAttendance
    //group name: project-attendances
    Route::get('/get-org-user-list', [ProjectAttendanceController::class, 'getOrgUse']);
    Route::get('/get-project-attendances', [ProjectAttendanceController::class, 'index']);
    Route::get('/get-project-attendance/{id}', [ProjectAttendanceController::class, 'show']);
    Route::post('/create-project-attendance', [ProjectAttendanceController::class, 'store']);
    Route::put('/update-project-attendance/{id}', [ProjectAttendanceController::class, 'update']);
    Route::delete('/delete-project-attendance/{id}', [ProjectAttendanceController::class, 'destroy']);

    //project Guest Attendance 
    //group name: project-guest-attendances
    Route::get('/get-project-guest-attendances', [ProjectGuestAttendanceController::class, 'index']);
    Route::get('/get-project-guest-attendance/{id}', [ProjectGuestAttendanceController::class, 'show']);
    Route::post('/create-project-guest-attendance', [ProjectGuestAttendanceController::class, 'store']);
    Route::put('/update-project-guest-attendance/{id}', [ProjectGuestAttendanceController::class, 'update']);
    Route::delete('/delete-project-guest-attendance/{id}', [ProjectGuestAttendanceController::class, 'destroy']);

    //Project Summery
    //group name: project-summaries
    Route::get('/get-project-summary', [ProjectSummaryController::class, 'index']);
    Route::get('/get-project-summary/{id}', [ProjectSummaryController::class, 'show']);
    Route::post('/create-project-summary', [ProjectSummaryController::class, 'store']);
    Route::put('/update-project-summary/{id}', [ProjectSummaryController::class, 'update']);
    Route::delete('/delete-project-summary/{id}', [ProjectSummaryController::class, 'destroy']);

    // Founder
    //group name: founders
    Route::post('create-founder', [FounderController::class, 'store']);
    Route::get('get-founder/{userId}', [FounderController::class, 'index']);
    Route::put('update-founder/{id}', [FounderController::class, 'update']);

    // Asset
    //group name: assets
    Route::get('/get-assets/{userId}', [AssetController::class, 'getAsset']);
    Route::get('/get-asset/{assetId}', [AssetController::class, 'getAssetDetails']);
    Route::post('/create-asset', [AssetController::class, 'store']);
    Route::post('/update-asset/{id}', [AssetController::class, 'update']);
    Route::delete('/delete-asset/{id}', [AssetController::class, 'destroy']);

    // privacy_setups
    //group name: privacy-setups
    Route::get('privacy-setups', [PrivacySetupController::class, 'index']);
    Route::get('/get-all-privacy-setups', [PrivacySetupController::class, 'getAllPrivacySetupForSuperAdmin']);
    Route::get('/get-privacy-setups', [PrivacySetupController::class, 'index']);
    Route::post('/create-privacy-setup', [PrivacySetupController::class, 'store']);
    Route::put('/update-privacy-setup/{id}', [PrivacySetupController::class, 'update']);
    Route::delete('/delete-privacy-setup/{id}', [PrivacySetupController::class, 'destroy']);

    // asset_lifecycle_setups
    //group name: asset-lifecycle-setups
    Route::get('asset-lifecycle-setups', [AssetLifecycleStatusController::class, 'index']);

    //Billing
    //group name: management-subscriptions
    Route::get('management-subscriptions', [ManagementSubscriptionController::class, 'show']);
    Route::post('management-subscriptions', [ManagementSubscriptionController::class, 'store']);
    Route::put('management-subscriptions/{id}', [ManagementSubscriptionController::class, 'update']);
    Route::delete('management-subscriptions{id}', [ManagementSubscriptionController::class, 'destroy']);

    //group name: no group (api name: management-pricings) only GET
    Route::get('/user-price-rate', [ManagementPricingController::class, 'getUserPriceRate']);


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
    Route::get('billing-list', [ManagementAndStorageBillingController::class, 'index']);
    Route::get('superadmin-billing-list', [ManagementAndStorageBillingController::class, 'indexSuperAdmin']);
    Route::get('get-billing/{id}', [ManagementAndStorageBillingController::class, 'show']);
    Route::post('create-billing', [ManagementAndStorageBillingController::class, 'store']);
    Route::post('system-create-billing', [ManagementAndStorageBillingController::class, 'storeBySystem']);
    Route::put('update-billing/{id}', [ManagementAndStorageBillingController::class, 'update']);
    Route::delete('delete-billing/{id}', [ManagementAndStorageBillingController::class, 'destroy']);

    // Everyday billing
    //group name: every-day-member-count-and-billings
    Route::get('every-day-member-count-and-bill-list', [EverydayMemberCountAndBillingController::class, 'index']);
    Route::get('get-every-day-member-count-and-bill/{id}', [EverydayMemberCountAndBillingController::class, 'show']);
    Route::post('create-every-day-member-count-and-bill', [EverydayMemberCountAndBillingController::class, 'superAdminStore']);
    Route::put('update-every-day-member-count-and-bill/{id}', [EverydayMemberCountAndBillingController::class, 'update']);
    Route::delete('delete-every-day-member-count-and-bill/{id}', [EverydayMemberCountAndBillingController::class, 'destroy']);

    // Everyday storage billing
    //group name: every-day-storage-billings
    Route::get('everyday-storage-billing-list', [EverydayStorageBillingController::class, 'index']);
    Route::get('get-everyday-storage-billing/{id}', [EverydayStorageBillingController::class, 'show']);
    Route::post('create-everyday-storage-billing', [EverydayStorageBillingController::class, 'superAdminStore']);
    Route::put('update-everyday-storage-billing/{id}', [EverydayStorageBillingController::class, 'update']);
    Route::delete('delete-everyday-storage-billing/{id}', [EverydayStorageBillingController::class, 'destroy']);

    // Invoice
    //group name: invoices
    Route::get('invoices', [InvoiceController::class, 'index']);
    Route::get('all-invoices', [InvoiceController::class, 'indexForSuperadmin']);
    Route::get('get-invoice/{id}', [InvoiceController::class, 'show']);
    Route::post('create-invoice', [InvoiceController::class, 'store']);
    Route::put('update-invoice/{id}', [InvoiceController::class, 'update']);
    Route::delete('delete-invoice/{id}', [InvoiceController::class, 'destroy']);

    // ManagementPricing
    //group name: management-pricings
    Route::get('price-rate', [ManagementPricingController::class, 'index']);
    Route::put('price-rate/update', [ManagementPricingController::class, 'update']);
    Route::get('/all-user-price-rate', [ManagementPricingController::class, 'getAllUserPriceRate']);

    //Currency
    //group name: currencies
    Route::get('currencies', [CurrencyController::class, 'index']);
    Route::post('currencies', [CurrencyController::class, 'store']);
    Route::put('currencies/{id}', [CurrencyController::class, 'update']);
    Route::delete('currencies/{id}', [CurrencyController::class, 'destroy']);

    // RegionalTaxRateController
    //group name: regional-tax-rates
    Route::get('/get-region-tax-rates', [RegionalTaxRateController::class, 'index']);
    Route::get('/get-region-tax-rate/{id}', [RegionalTaxRateController::class, 'show']);
    Route::post('/create-region-tax-rate', [RegionalTaxRateController::class, 'store']);
    Route::put('/update-region-tax-rate/{id}', [RegionalTaxRateController::class, 'update']);
    Route::delete('/delete-region-tax-rate/{id}', [RegionalTaxRateController::class, 'destroy']);

    // Country
    //group name: countries
    Route::get('/get-countries', [CountryController::class, 'index']);
    Route::post('/create-country', [CountryController::class, 'store']);
    Route::put('/update-country/{id}', [CountryController::class, 'update']);
    Route::delete('/delete-country/{id}', [CountryController::class, 'destroy']);

    // dialing-code
    //group name: dialing-codes
    Route::get('/get-dialing-codes', [DialingCodeController::class, 'index']);
    Route::post('/create-dialing-code', [DialingCodeController::class, 'store']);
    Route::put('/update-dialing-code/{id}', [DialingCodeController::class, 'update']);
    Route::delete('/delete-dialing-code/{id}', [DialingCodeController::class, 'destroy']);


    // ConductTypeController
    //group name: conduct-types
    Route::get('/get-conduct-types', [ConductTypeController::class, 'index']);
    Route::post('/create-conduct-type', [ConductTypeController::class, 'store']);
    Route::put('/update-conduct-type/{id}', [ConductTypeController::class, 'update']);
    Route::delete('/delete-conduct-type/{id}', [ConductTypeController::class, 'destroy']);

    // AttendanceTypeController
    //group name: attendance-types
    Route::get('/get-attendance-types', [AttendanceTypeController::class, 'index']);
    Route::post('/create-attendance-type', [AttendanceTypeController::class, 'store']);
    Route::put('/update-attendance-type/{id}', [AttendanceTypeController::class, 'update']);
    Route::delete('/delete-attendance-type/{id}', [AttendanceTypeController::class, 'destroy']);

    // membership-type
    //group name: membership-types
    Route::get('/get-membership-types', [MembershipTypeController::class, 'index']);
    Route::post('/create-membership-type', [MembershipTypeController::class, 'store']);
    Route::put('/update-membership-type/{id}', [MembershipTypeController::class, 'update']);
    Route::delete('/delete-membership-type/{id}', [MembershipTypeController::class, 'destroy']);

    // designation
    //group name: designations
    Route::get('/get-designations', [DesignationController::class, 'index']);
    Route::post('/create-designation', [DesignationController::class, 'store']);
    Route::put('/update-designation/{id}', [DesignationController::class, 'update']);
    Route::delete('/delete-designation/{id}', [DesignationController::class, 'destroy']);

    // Languages
    //group name: languages
    Route::get('/get-languages', [LanguageListController::class, 'index']);
    Route::post('/create-language', [LanguageListController::class, 'store']);
    Route::put('/update-language/{id}', [LanguageListController::class, 'update']);
    Route::delete('/delete-language/{id}', [LanguageListController::class, 'destroy']);

    // time-zone-setup
    //group name: time-zone-setups
    Route::get('/get-time-zone-setups', [TimeZoneSetupController::class, 'index']);
    Route::post('/create-time-zone-setup', [TimeZoneSetupController::class, 'store']);
    Route::put('/update-time-zone-setup/{id}', [TimeZoneSetupController::class, 'update']);
    Route::delete('/delete-time-zone-setup/{id}', [TimeZoneSetupController::class, 'destroy']);

    // User countries
    //group name: user-countries
    Route::get('/get-user-list', [UserCountryController::class, 'getUser']);
    Route::get('/get-user-countries', [UserCountryController::class, 'index']);
    Route::post('/create-user-country', [UserCountryController::class, 'store']);
    Route::put('/update-user-country/{id}', [UserCountryController::class, 'update']);
    Route::delete('/delete-user-country/{id}', [UserCountryController::class, 'destroy']);

    // RegionController
    //group name: regions
    Route::get('/get-regions', [RegionController::class, 'index']);
    Route::get('/get-region/{id}', [RegionController::class, 'show']);
    Route::post('/create-region', [RegionController::class, 'store']);
    Route::put('/update-region/{id}', [RegionController::class, 'update']);
    Route::delete('/delete-region/{id}', [RegionController::class, 'destroy']);

    // CountryRegionController
    //group name: country-regions
    Route::get('/get-country-regions', [CountryRegionController::class, 'index']);
    Route::get('/get-country-region/{id}', [CountryRegionController::class, 'show']);
    Route::post('/create-country-region', [CountryRegionController::class, 'store']);
    Route::put('/update-country-region/{id}', [CountryRegionController::class, 'update']);
    Route::delete('/delete-country-region/{id}', [CountryRegionController::class, 'destroy']);

    // RegionCurrencyController
    //group name: region-currencies
    Route::get('/get-region-currencies', [RegionCurrencyController::class, 'index']);
    Route::get('/get-region-currency/{id}', [RegionCurrencyController::class, 'show']);
    Route::post('/create-region-currency', [RegionCurrencyController::class, 'store']);
    Route::put('/update-region-currency/{id}', [RegionCurrencyController::class, 'update']);
    Route::delete('/delete-region-currency/{id}', [RegionCurrencyController::class, 'destroy']);

    //BusinessTypeController
    //group name: business-types
    Route::get('get-business-types', [BusinessTypeController::class, 'index']);
    Route::post('get-business-type{id}', [BusinessTypeController::class, 'show']);
    Route::post('create-business-type', [BusinessTypeController::class, 'store']);
    Route::put('update-business-type/{id}', [BusinessTypeController::class, 'update']);
    Route::delete('delete-business-type/{id}', [BusinessTypeController::class, 'destroy']);

    // CategoryController
    //group name: categories
    Route::get('get-categories', [CategoryController::class, 'index']);
    Route::get('get-category/{id}', [CategoryController::class, 'show']);
    Route::post('create-category', [CategoryController::class, 'store']);
    Route::put('update-category/{id}', [CategoryController::class, 'update']);
    Route::delete('delete-category/{id}', [CategoryController::class, 'destroy']);

    // SubCategoryController
    //group name: sub-categories
    Route::get('get-sub-categories', [SubCategoryController::class, 'index']);
    Route::get('get-sub-category/{id}', [SubCategoryController::class, 'show']);
    Route::post('create-sub-category', [SubCategoryController::class, 'store']);
    Route::put('update-sub-category/{id}', [SubCategoryController::class, 'update']);
    Route::delete('delete-sub-category/{id}', [SubCategoryController::class, 'destroy']);

    // SubSubCategoryController
    //group name: sub-sub-categories
    Route::get('get-sub-sub-categories', [SubSubCategoryController::class, 'index']);
    Route::get('get-sub-sub-category/{id}', [SubSubCategoryController::class, 'show']);
    Route::post('create-sub-sub-category', [SubSubCategoryController::class, 'store']);
    Route::put('update-sub-sub-category/{id}', [SubSubCategoryController::class, 'update']);
    Route::delete('delete-sub-sub-category/{id}', [SubSubCategoryController::class, 'destroy']);

    // BrandController
    //group name: brands
    Route::get('get-brands', [BrandController::class, 'index']);
    Route::get('get-brand/{id}', [BrandController::class, 'show']);
    Route::post('create-brand', [BrandController::class, 'store']);
    Route::put('update-brand/{id}', [BrandController::class, 'update']);
    Route::delete('delete-brand/{id}', [BrandController::class, 'destroy']);

    // ProductController
    //group name: products
    Route::get('get-products', [ProductController::class, 'index']);
    Route::get('get-product/{id}', [ProductController::class, 'show']);
    Route::post('create-product', [ProductController::class, 'store']);
    Route::put('update-product/{id}', [ProductController::class, 'update']);
    Route::delete('delete-product/{id}', [ProductController::class, 'destroy']);

    //...................... E-commerce...............................................
    // OrderController
    //group name: orders
    Route::get('get-orders', [OrderController::class, 'index']);
    Route::get('get-order/{id}', [OrderController::class, 'show']);
    Route::post('create-order', [OrderController::class, 'store']);
    Route::put('update-order/{id}', [OrderController::class, 'update']);
    Route::delete('delete-order/{id}', [OrderController::class, 'destroy']);

    // OrderItemController
    //group name: order-items
    Route::get('get-order-items', [OrderItemController::class, 'index']);
    Route::get('get-order-item/{id}', [OrderItemController::class, 'show']);
    Route::post('create-order-item', [OrderItemController::class, 'store']);
    Route::put('update-order-item/{id}', [OrderItemController::class, 'update']);
    Route::delete('delete-order-item/{id}', [OrderItemController::class, 'destroy']);

    //OrderDetailController
    //group name: order-details
    Route::get('get-order-details', [OrderDetailController::class, 'index']);
    Route::get('get-order-detail/{id}', [OrderDetailController::class, 'show']);
    Route::post('create-order-detail', [OrderDetailController::class, 'store']);
    Route::put('update-order-detail/{id}', [OrderDetailController::class, 'update']);
    Route::delete('delete-order-detail/{id}', [OrderDetailController::class, 'destroy']);
});
