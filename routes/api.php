<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Individual\IndividualController;

//Org members
use App\Http\Controllers\Org\OrgMemberController;
use App\Http\Controllers\Org\OrgIndependentMemberController;

//Member counting
use App\Http\Controllers\OrgMemberCountController;


//Committee
use App\Http\Controllers\Org\CommitteeController;
use App\Http\Controllers\Org\CommitteeMemberController;

//Meeting
use App\Http\Controllers\Org\MeetingController;
use App\Http\Controllers\Org\MeetingMinutesController;
use App\Http\Controllers\Org\MeetingAttendanceController;
use App\Http\Controllers\MeetingGuestAttendanceController;

use App\Http\Controllers\Org\AddressController;
use App\Http\Controllers\Org\OrgAdministratorController;
use App\Http\Controllers\PhoneNumberController;

//Event Controller
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventAttendanceController;
use App\Http\Controllers\EventSummaryController;
use App\Http\Controllers\EventGuestAttendanceController;


//Project
use App\Http\Controllers\ProjectAttendanceController;
use App\Http\Controllers\ProjectSummaryController;
use App\Http\Controllers\Org\ProjectController;
use App\Http\Controllers\ProjectGuestAttendanceController;


use App\Http\Controllers\SuperAdmin\SuperAdminController;

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Org\FounderController;
use App\Http\Controllers\Org\OrgProfileController;

use App\Http\Controllers\SuccessStoryController;
use App\Http\Controllers\StrategicPlanController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\YearPlanController;
use App\Http\Controllers\RecognitionController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountFundController;
use App\Http\Controllers\OrgReportController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\PrivacySetupController;
use App\Http\Controllers\AssetLifecycleStatusController;

//Org office record
use App\Http\Controllers\OfficeRecordController;


// Billing
use App\Http\Controllers\PackageController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ActiveMemberCountController;
use App\Http\Controllers\HonoraryMemberCountController;
use App\Http\Controllers\ManagementAndStorageBillingController;
use App\Http\Controllers\EverydayMemberCountAndBillingController;
use App\Http\Controllers\EverydayStorageBillingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CurrencyController;


// Master Setting
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DialingCodeController;
use App\Http\Controllers\AttendanceTypeController;
use App\Http\Controllers\ConductTypeController;
use App\Http\Controllers\Org\MembershipTypeController;
use App\Http\Controllers\Org\DesignationController;
use App\Http\Controllers\LanguageListController;
use App\Http\Controllers\RegionalPricingController;
use App\Http\Controllers\TimeZoneSetupController;
use App\Http\Controllers\UserCountryController;

use App\Http\Controllers\RegionController;
use App\Http\Controllers\CountryRegionController;
use App\Http\Controllers\RegionCurrencyController;
use App\Http\Controllers\PaymentLogController;
use App\Http\Controllers\RegionalTaxRateController;

use App\Http\Controllers\BusinessTypeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\SubSubCategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReceiptController;


//API for auth
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('/verify-account/{uuid}', [AuthController::class, 'verify']);

//Route::get('get-independent-members', [OrgIndependentMemberController::class, 'index']);



Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);

    // ------------------- Individual----------------------------------------------------------------
    Route::get('/individual_profile_data/{userId}', [IndividualController::class, 'getProfileImage']);
    Route::get('/profileimage/{userId}', [IndividualController::class, 'getProfileImage']);
    Route::post('/profileimage/{userId}', [IndividualController::class, 'updateProfileImage']);
    Route::get('/connected-org-list/{userId}', [IndividualController::class, 'getOrganisationByIndividualId']);

    Route::get('/individual-users', [IndividualController::class, 'getIndividualUser']);


    // ------------------- Organisation----------------------------------------------------------------

    //Billing
    Route::get('billing-list', [ManagementAndStorageBillingController::class, 'index']);


    // Accounts
    Route::get('/get-transactions', [AccountController::class, 'getTransactions']);
    Route::post('/create-transaction', [AccountController::class, 'createTransaction']);
    Route::put('/update-transaction/{id}', [AccountController::class, 'updateTransaction']);
    Route::delete('/delete-transaction/{id}', [AccountController::class, 'deleteTransaction']);

    // Funds
    Route::get('/get-funds', [AccountFundController::class, 'index']);
    Route::post('/create-fund', [AccountFundController::class, 'store']);
    Route::put('/update-fund/{id}', [AccountFundController::class, 'update']);
    Route::delete('/delete-fund/{id}', [AccountFundController::class, 'destroy']);

    // HistoryController
    Route::get('/get-org-histories', [HistoryController::class, 'index']);
    Route::get('/get-org-history/{id}', [HistoryController::class, 'show']);
    Route::post('/create-org-history', [HistoryController::class, 'store']);
    Route::put('/update-org-history/{id}', [HistoryController::class, 'update']);
    Route::delete('/delete-org-history/{id}', [HistoryController::class, 'destroy']);

    //Year plan
    Route::get('/get-year-plans', [YearPlanController::class, 'index']);
    Route::get('/year-plan/{id}', [YearPlanController::class, 'show']);
    Route::post('/create-year-plan', [YearPlanController::class, 'store']);
    Route::put('/update-year-plan/{id}', [YearPlanController::class, 'update']);
    Route::delete('/delete-year-plan/{id}', [YearPlanController::class, 'destroy']);

    // Recognition
    Route::get('/get-recognitions', [RecognitionController::class, 'index']);
    Route::get('/get-recognition/{id}', [RecognitionController::class, 'show']);
    Route::post('/create-recognition', [RecognitionController::class, 'store']);
    Route::put('/update-recognition/{id}', [RecognitionController::class, 'update']);
    Route::delete('/delete-recognition/{id}', [RecognitionController::class, 'destroy']);

    // StrategicPlan
    Route::get('/get-strategic-plans', [StrategicPlanController::class, 'index']);
    Route::get('/get-strategic-plan/{id}', [StrategicPlanController::class, 'show']);
    Route::post('/create-strategic-plan', [StrategicPlanController::class, 'store']);
    Route::put('/update-strategic-plan/{id}', [StrategicPlanController::class, 'update']);
    Route::delete('/delete-strategic-plan/{id}', [StrategicPlanController::class, 'destroy']);

    // SuccessStoryController
    Route::get('/get-records', [SuccessStoryController::class, 'index']);
    Route::get('/get-record/{id}', [SuccessStoryController::class, 'show']);
    Route::post('/create-record', [SuccessStoryController::class, 'store']);
    Route::put('/update-record/{id}', [SuccessStoryController::class, 'update']);
    Route::delete('/delete-record/{id}', [SuccessStoryController::class, 'destroy']);

    //update org user info  
    Route::put('update-name/{userId}', [AuthController::class, 'nameUpdate']);
    Route::put('update-username/{userId}', [AuthController::class, 'usernameUpdate']);
    Route::put('update-email/{userId}', [AuthController::class, 'userEmailUpdate']);
    Route::put('update-password/{userId}', [AuthController::class, 'updatePassword']);

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
    Route::get('/get-office-records', [OfficeRecordController::class, 'index']);
    Route::get('/get-office-record/{recordId}', [OfficeRecordController::class, 'show']);
    Route::post('/create-office-record', [OfficeRecordController::class, 'store']);
    Route::put('/update-office-record/{id}', action: [OfficeRecordController::class, 'update']);
    Route::delete('/delete-office-record/{id}', [OfficeRecordController::class, 'destroy']);

    //API for org membership
    Route::post('/search_individual', [OrgMemberController::class, 'search']);
    Route::post('/add_member', [OrgMemberController::class, 'addMember']);
    Route::get('/org-members/{userId}', [OrgMemberController::class, 'getOrgMembers']);
    Route::get('/org-member-list/{userId}', [OrgMemberController::class, 'getMemberList']);
    Route::get('/org-all-member-list', [OrgMemberController::class, 'getOrgAllMemberList']);
    Route::get('/total-org-member-count/{userId}', [OrgMemberController::class, 'totalOrgMemberCount']);

    // OrgIndependentMemberController
    Route::get('get-independent-members', [OrgIndependentMemberController::class, 'index']);
    Route::get('get-independent-member/{id}', [OrgIndependentMemberController::class, 'show']);
    Route::post('create-independent-member', [OrgIndependentMemberController::class, 'store']);
    Route::put('update-independent-member/{id}', [OrgIndependentMemberController::class, 'update']);
    Route::delete('delete-independent-member/{id}', [OrgIndependentMemberController::class, 'destroy']);

    //Org member count
    Route::post('/org-member-counts', [OrgMemberCountController::class, 'store']);


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
    Route::get('org-committee-list/{userId}', [CommitteeController::class, 'getCommitteeListByUserId']);
    Route::post('create_committee', [CommitteeController::class, 'store']);
    Route::put('update_committee/{id}', [CommitteeController::class, 'update']);
    Route::delete('org-committee/{id}', [CommitteeController::class, 'destroy']);

    //Committee Member
    Route::get('/get-committee-members', [CommitteeMemberController::class, 'index']);
    Route::get('/get-committee-member/{id}', [CommitteeMemberController::class, 'show']);
    Route::post('/create-committee-member', [CommitteeMemberController::class, 'store']);
    Route::put('/update-committee-member/{id}', [CommitteeMemberController::class, 'update']);
    Route::delete('/delete-committee-member/{id}', [CommitteeMemberController::class, 'destroy']);

    //Meeting
    // Route::get('/get-meetings', [MeetingController::class, 'index']);
    Route::get('/get-meeting/{id}', [MeetingController::class, 'show']); // Fetch meeting details
    Route::get('/get-org-meetings', [MeetingController::class, 'getOrgMeeting']);
    Route::post('/create-meeting', [MeetingController::class, 'store']);
    Route::put('/update-meeting/{id}', [MeetingController::class, 'update']);
    Route::delete('/delete-meeting/{id}', [MeetingController::class, 'destroy']);

    // meeting-minutes
    Route::get('/get-meeting-minutes', [MeetingMinutesController::class, 'index']);
    Route::get('/get-meeting-minutes/{id}', [MeetingMinutesController::class, 'show']);
    Route::post('/create-meeting-minutes', [MeetingMinutesController::class, 'store']);
    Route::post('/update-meeting-minutes/{id}', [MeetingMinutesController::class, 'update']);
    Route::delete('/delete-meeting-minutes/{id}', [MeetingMinutesController::class, 'destroy']);

    //Meeting MeetingAttendance 
    Route::get('/get-org-user-list', [MeetingAttendanceController::class, 'getOrgUse']);
    Route::get('/get-meeting-attendances', [MeetingAttendanceController::class, 'index']);
    Route::get('/get-meeting-attendance/{id}', [MeetingAttendanceController::class, 'show']);
    Route::post('/create-meeting-attendance', [MeetingAttendanceController::class, 'store']);
    Route::put('/update-meeting-attendance/{id}', [MeetingAttendanceController::class, 'update']);
    Route::delete('/delete-meeting-attendance/{id}', [MeetingAttendanceController::class, 'destroy']);

    //Meeting MeetingGuestAttendance 
    Route::get('/get-meeting-guest-attendances', [MeetingGuestAttendanceController::class, 'index']);
    Route::get('/get-meeting-guest-attendance/{id}', [MeetingGuestAttendanceController::class, 'show']);
    Route::post('/create-meeting-guest-attendance', [MeetingGuestAttendanceController::class, 'store']);
    Route::put('/update-meeting-guest-attendance/{id}', [MeetingGuestAttendanceController::class, 'update']);
    Route::delete('/delete-meeting-guest-attendance/{id}', [MeetingGuestAttendanceController::class, 'destroy']);

    //Event  
    Route::get('/get-events/{userId}', [EventController::class, 'index']);
    Route::get('/get-event/{eventId}', [EventController::class, 'getEvent']);
    Route::post('/create-event', [EventController::class, 'createEvent']);
    Route::put('/update-event/{eventId}', [EventController::class, 'updateEvent']);
    Route::delete('/delete-event/{eventId}', [EventController::class, 'deleteEvent']);

    // Event Attendance
    Route::get('/get-org-user-list', [EventAttendanceController::class, 'getOrgUse']);
    Route::get('/get-event-attendances', [EventAttendanceController::class, 'index']);
    Route::get('/get-event-attendance/{id}', [EventAttendanceController::class, 'show']);
    Route::post('/create-event-attendance', [EventAttendanceController::class, 'store']);
    Route::put('/update-event-attendance/{id}', [EventAttendanceController::class, 'update']);
    Route::delete('/delete-event-attendance/{id}', [EventAttendanceController::class, 'destroy']);

    //Event Guest Attendance 
    Route::get('/get-event-guest-attendances', [EventGuestAttendanceController::class, 'index']);
    Route::get('/get-event-guest-attendance/{id}', [EventGuestAttendanceController::class, 'show']);
    Route::post('/create-event-guest-attendance', [EventGuestAttendanceController::class, 'store']);
    Route::put('/update-event-guest-attendance/{id}', [EventGuestAttendanceController::class, 'update']);
    Route::delete('/delete-event-guest-attendance/{id}', [EventGuestAttendanceController::class, 'destroy']);

    // event-summary
    Route::get('/get-event-summary', [EventSummaryController::class, 'index']);
    Route::get('/get-event-summary/{id}', [EventSummaryController::class, 'show']);
    Route::post('/create-event-summary', [EventSummaryController::class, 'store']);
    Route::put('/update-event-summary/{id}', [EventSummaryController::class, 'update']);
    Route::delete('/delete-event-summary/{id}', [EventSummaryController::class, 'destroy']);


    //Project
    Route::get('org-project-list/{userId}', [ProjectController::class, 'index']);
    Route::get('/get-project/{projectId}', [ProjectController::class, 'show']);
    Route::post('create-project', [ProjectController::class, 'store']);
    Route::put('update-project/{userId}', [ProjectController::class, 'update']);
    Route::delete('/delete-project/{id}', [ProjectController::class, 'destroy']);

    //ProjectAttendance
    Route::get('/get-org-user-list', [ProjectAttendanceController::class, 'getOrgUse']);
    Route::get('/get-project-attendances', [ProjectAttendanceController::class, 'index']);
    Route::get('/get-project-attendance/{id}', [ProjectAttendanceController::class, 'show']);
    Route::post('/create-project-attendance', [ProjectAttendanceController::class, 'store']);
    Route::put('/update-project-attendance/{id}', [ProjectAttendanceController::class, 'update']);
    Route::delete('/delete-project-attendance/{id}', [ProjectAttendanceController::class, 'destroy']);

    //project Guest Attendance 
    Route::get('/get-project-guest-attendances', [ProjectGuestAttendanceController::class, 'index']);
    Route::get('/get-project-guest-attendance/{id}', [ProjectGuestAttendanceController::class, 'show']);
    Route::post('/create-project-guest-attendance', [ProjectGuestAttendanceController::class, 'store']);
    Route::put('/update-project-guest-attendance/{id}', [ProjectGuestAttendanceController::class, 'update']);
    Route::delete('/delete-project-guest-attendance/{id}', [ProjectGuestAttendanceController::class, 'destroy']);

    //Project Summery
    Route::get('/get-project-summary', [ProjectSummaryController::class, 'index']);
    Route::get('/get-project-summary/{id}', [ProjectSummaryController::class, 'show']);
    Route::post('/create-project-summary', [ProjectSummaryController::class, 'store']);
    Route::put('/update-project-summary/{id}', [ProjectSummaryController::class, 'update']);
    Route::delete('/delete-project-summary/{id}', [ProjectSummaryController::class, 'destroy']);

    // Founder
    Route::post('create-founder', [FounderController::class, 'store']);
    Route::get('get-founder/{userId}', [FounderController::class, 'index']);
    Route::put('update-founder/{id}', [FounderController::class, 'update']);

    // Asset
    Route::get('/get-assets/{userId}', [AssetController::class, 'getAsset']);
    Route::get('/get-asset/{assetId}', [AssetController::class, 'getAssetDetails']);
    Route::post('/create-asset', [AssetController::class, 'store']);
    Route::put('/update-asset/{id}', [AssetController::class, 'update']);
    Route::delete('/delete-asset/{id}', [AssetController::class, 'destroy']);

    // privacy_setups
    Route::get('privacy-setups', [PrivacySetupController::class, 'index']);
    // privacy_setups
    Route::get('/get-all-privacy-setups', [PrivacySetupController::class, 'getAllPrivacySetupForSuperAdmin']);
    Route::get('/get-privacy-setups', [PrivacySetupController::class, 'index']);
    Route::post('/create-privacy-setup', [PrivacySetupController::class, 'store']);
    Route::put('/update-privacy-setup/{id}', [PrivacySetupController::class, 'update']);
    Route::delete('/delete-privacy-setup/{id}', [PrivacySetupController::class, 'destroy']);

    // asset_lifecycle_setups
    Route::get('asset-lifecycle-setups', [AssetLifecycleStatusController::class, 'index']);

    //Billing
    Route::get('packages', [PackageController::class, 'index']);

    Route::get('subscription', [SubscriptionController::class, 'show']);
    Route::post('subscription', [SubscriptionController::class, 'store']);
    Route::put('subscription/{id}', [SubscriptionController::class, 'update']);
    Route::delete('subscription{id}', [SubscriptionController::class, 'destroy']);
    Route::get('/user-price-rate', [RegionalPricingController::class, 'getUserPriceRate']);


    // ------------------- SuperAdmin----------------------------------------------------------------
    //API for SuperAdmin
    Route::get('/super_admin_user_data/{id}', [SuperAdminController::class, 'show']);

    //Finance
    Route::get('billing-list', [ManagementAndStorageBillingController::class, 'index']);
    Route::get('superadmin-billing-list', [ManagementAndStorageBillingController::class, 'indexSuperAdmin']);
    Route::get('get-billing/{id}', [ManagementAndStorageBillingController::class, 'show']);
    Route::post('create-billing', [ManagementAndStorageBillingController::class, 'store']);
    Route::post('system-create-billing', [ManagementAndStorageBillingController::class, 'storeBySystem']);
    Route::put('update-billing/{id}', [ManagementAndStorageBillingController::class, 'update']);
    Route::delete('delete-billing/{id}', [ManagementAndStorageBillingController::class, 'destroy']);



    // Everyday billing
    Route::get('every-day-member-count-and-bill-list', [EverydayMemberCountAndBillingController::class, 'index']);
    Route::get('get-every-day-member-count-and-bill/{id}', [EverydayMemberCountAndBillingController::class, 'show']);
    Route::post('create-every-day-member-count-and-bill', [EverydayMemberCountAndBillingController::class, 'superAdminStore']);
    Route::put('update-every-day-member-count-and-bill/{id}', [EverydayMemberCountAndBillingController::class, 'update']);
    Route::delete('delete-every-day-member-count-and-bill/{id}', [EverydayMemberCountAndBillingController::class, 'destroy']);

    Route::get('everyday-storage-billing-list', [EverydayStorageBillingController::class, 'index']);
    Route::get('get-everyday-storage-billing/{id}', [EverydayStorageBillingController::class, 'show']);
    Route::post('create-everyday-storage-billing', [EverydayStorageBillingController::class, 'superAdminStore']);
    Route::put('update-everyday-storage-billing/{id}', [EverydayStorageBillingController::class, 'update']);
    Route::delete('delete-everyday-storage-billing/{id}', [EverydayStorageBillingController::class, 'destroy']);

    Route::get('invoices', [InvoiceController::class, 'index']);
    Route::get('all-invoices', [InvoiceController::class, 'indexForSuperadmin']);
    Route::get('get-invoice/{id}', [InvoiceController::class, 'show']);
    Route::post('create-invoice', [InvoiceController::class, 'store']);
    Route::put('update-invoice/{id}', [InvoiceController::class, 'update']);
    Route::delete('delete-invoice/{id}', [InvoiceController::class, 'destroy']);

    Route::get('subscription-plans', [SubscriptionController::class, 'index']);

    // PriceRate
    Route::get('price-rate', [RegionalPricingController::class, 'index']);
    Route::put('price-rate/update', [RegionalPricingController::class, 'update']);
    Route::get('/all-user-price-rate', [RegionalPricingController::class, 'getAllUserPriceRate']);

    //Currency
    Route::get('currencies', [CurrencyController::class, 'index']);
    Route::post('currencies', [CurrencyController::class, 'store']);
    Route::put('currencies/{id}', [CurrencyController::class, 'update']);
    Route::delete('currencies/{id}', [CurrencyController::class, 'destroy']);

    //Payment method

    // //Payment gateway
    // Route::get('payment-gateways', [PaymentGatewayController::class, 'index']);
    // Route::post('payment-gateways', [PaymentGatewayController::class,'store']);
    // Route::put('payment-gateways/{id}', [PaymentGatewayController::class, 'update']);
    // Route::delete('payment-gateways/{id}', [PaymentGatewayController::class, 'destroy']);

    // //Subscription plan
    // Route::get('subscription-plans', [SubscriptionPlanController::class, 'index']);
    // Route::post('subscription-plans', [SubscriptionPlanController::class,'store']);

    // RegionalTaxRateController
    Route::get('/get-region-tax-rates', [RegionalTaxRateController::class, 'index']);
    Route::get('/get-region-tax-rate/{id}', [RegionalTaxRateController::class, 'show']);
    Route::post('/create-region-tax-rate', [RegionalTaxRateController::class, 'store']);
    Route::put('/update-region-tax-rate/{id}', [RegionalTaxRateController::class, 'update']);
    Route::delete('/delete-region-tax-rate/{id}', [RegionalTaxRateController::class, 'destroy']);

    // PaymentLogController
    Route::get('payment-logs', [PaymentLogController::class, 'index']);
    Route::get('get-payment-log/{id}', [PaymentLogController::class, 'show']);
    Route::post('create-payment-log', [PaymentLogController::class, 'store']);
    Route::put('update-payment-log/{id}', [PaymentLogController::class, 'update']);
    Route::delete('delete-payment-log/{id}', [PaymentLogController::class, 'destroy']);

    // Country
    Route::get('/get-countries', [CountryController::class, 'index']);
    Route::post('/create-country', [CountryController::class, 'store']);
    Route::put('/update-country/{id}', [CountryController::class, 'update']);
    Route::delete('/delete-country/{id}', [CountryController::class, 'destroy']);
    // dialing-code
    Route::get('/get-dialing-codes', [DialingCodeController::class, 'index']);
    Route::post('/create-dialing-code', [DialingCodeController::class, 'store']);
    Route::put('/update-dialing-code/{id}', [DialingCodeController::class, 'update']);
    Route::delete('/delete-dialing-code/{id}', [DialingCodeController::class, 'destroy']);


    // ConductTypeController
    Route::get('/get-conduct-types', [ConductTypeController::class, 'index']);
    Route::post('/create-conduct-type', [ConductTypeController::class, 'store']);
    Route::put('/update-conduct-type/{id}', [ConductTypeController::class, 'update']);
    Route::delete('/delete-conduct-type/{id}', [ConductTypeController::class, 'destroy']);

    // AttendanceTypeController
    Route::get('/get-attendance-types', [AttendanceTypeController::class, 'index']);
    Route::post('/create-attendance-type', [AttendanceTypeController::class, 'store']);
    Route::put('/update-attendance-type/{id}', [AttendanceTypeController::class, 'update']);
    Route::delete('/delete-attendance-type/{id}', [AttendanceTypeController::class, 'destroy']);
    // membership-type
    Route::get('/get-membership-types', [MembershipTypeController::class, 'index']);
    Route::post('/create-membership-type', [MembershipTypeController::class, 'store']);
    Route::put('/update-membership-type/{id}', [MembershipTypeController::class, 'update']);
    Route::delete('/delete-membership-type/{id}', [MembershipTypeController::class, 'destroy']);
    // designation
    Route::get('/get-designations', [DesignationController::class, 'index']);
    Route::post('/create-designation', [DesignationController::class, 'store']);
    Route::put('/update-designation/{id}', [DesignationController::class, 'update']);
    Route::delete('/delete-designation/{id}', [DesignationController::class, 'destroy']);
    // designation
    Route::get('/get-languages', [LanguageListController::class, 'index']);
    Route::post('/create-language', [LanguageListController::class, 'store']);
    Route::put('/update-language/{id}', [LanguageListController::class, 'update']);
    Route::delete('/delete-language/{id}', [LanguageListController::class, 'destroy']);
    // time-zone-setup
    Route::get('/get-time-zone-setups', [TimeZoneSetupController::class, 'index']);
    Route::post('/create-time-zone-setup', [TimeZoneSetupController::class, 'store']);
    Route::put('/update-time-zone-setup/{id}', [TimeZoneSetupController::class, 'update']);
    Route::delete('/delete-time-zone-setup/{id}', [TimeZoneSetupController::class, 'destroy']);
    // time-zone-setup
    Route::get('/get-user-list', [UserCountryController::class, 'getUser']);
    Route::get('/get-user-countries', [UserCountryController::class, 'index']);
    Route::post('/create-user-country', [UserCountryController::class, 'store']);
    Route::put('/update-user-country/{id}', [UserCountryController::class, 'update']);
    Route::delete('/delete-user-country/{id}', [UserCountryController::class, 'destroy']);

    // RegionController
    Route::get('/get-regions', [RegionController::class, 'index']);
    Route::get('/get-region/{id}', [RegionController::class, 'show']);
    Route::post('/create-region', [RegionController::class, 'store']);
    Route::put('/update-region/{id}', [RegionController::class, 'update']);
    Route::delete('/delete-region/{id}', [RegionController::class, 'destroy']);
    // CountryRegionController
    Route::get('/get-country-regions', [CountryRegionController::class, 'index']);
    Route::get('/get-country-region/{id}', [CountryRegionController::class, 'show']);
    Route::post('/create-country-region', [CountryRegionController::class, 'store']);
    Route::put('/update-country-region/{id}', [CountryRegionController::class, 'update']);
    Route::delete('/delete-country-region/{id}', [CountryRegionController::class, 'destroy']);
    // RegionCurrencyController
    Route::get('/get-region-currencies', [RegionCurrencyController::class, 'index']);
    Route::get('/get-region-currency/{id}', [RegionCurrencyController::class, 'show']);
    Route::post('/create-region-currency', [RegionCurrencyController::class, 'store']);
    Route::put('/update-region-currency/{id}', [RegionCurrencyController::class, 'update']);
    Route::delete('/delete-region-currency/{id}', [RegionCurrencyController::class, 'destroy']);

    //BusinessTypeController
    Route::get('get-business-types', [BusinessTypeController::class, 'index']);
    Route::post('get-business-type{id}', [BusinessTypeController::class, 'show']);
    Route::post('create-business-type', [BusinessTypeController::class, 'store']);
    Route::put('update-business-type/{id}', [BusinessTypeController::class, 'update']);
    Route::delete('delete-business-type/{id}', [BusinessTypeController::class, 'destroy']);

    // CategoryController
    Route::get('get-categories', [CategoryController::class, 'index']);
    Route::get('get-category/{id}', [CategoryController::class, 'show']);
    Route::post('create-category', [CategoryController::class, 'store']);
    Route::put('update-category/{id}', [CategoryController::class, 'update']);
    Route::delete('delete-category/{id}', [CategoryController::class, 'destroy']);
    // SubCategoryController
    Route::get('get-sub-categories', [SubCategoryController::class, 'index']);
    Route::get('get-sub-category/{id}', [SubCategoryController::class, 'show']);
    Route::post('create-sub-category', [SubCategoryController::class, 'store']);
    Route::put('update-sub-category/{id}', [SubCategoryController::class, 'update']);
    Route::delete('delete-sub-category/{id}', [SubCategoryController::class, 'destroy']);
    // SubSubCategoryController
    Route::get('get-sub-sub-categories', [SubSubCategoryController::class, 'index']);
    Route::get('get-sub-sub-category/{id}', [SubSubCategoryController::class, 'show']);
    Route::post('create-sub-sub-category', [SubSubCategoryController::class, 'store']);
    Route::put('update-sub-sub-category/{id}', [SubSubCategoryController::class, 'update']);
    Route::delete('delete-sub-sub-category/{id}', [SubSubCategoryController::class, 'destroy']);
    // BrandController
    Route::get('get-brands', [BrandController::class, 'index']);
    Route::get('get-brand/{id}', [BrandController::class, 'show']);
    Route::post('create-brand', [BrandController::class, 'store']);
    Route::put('update-brand/{id}', [BrandController::class, 'update']);
    Route::delete('delete-brand/{id}', [BrandController::class, 'destroy']);
    // ProductController
    Route::get('get-products', [ProductController::class, 'index']);
    Route::get('get-product/{id}', [ProductController::class, 'show']);
    Route::post('create-product', [ProductController::class, 'store']);
    Route::put('update-product/{id}', [ProductController::class, 'update']);
    Route::delete('delete-product/{id}', [ProductController::class, 'destroy']);
    
    // OrderController
    Route::get('get-orders', [OrderController::class, 'index']);
    Route::get('get-order/{id}', [OrderController::class, 'show']);
    Route::post('create-order', [OrderController::class, 'store']);
    Route::put('update-order/{id}', [OrderController::class, 'update']);
    Route::delete('delete-order/{id}', [OrderController::class, 'destroy']);

    // ReceiptController
    Route::get('get-receipts', [ReceiptController::class, 'index']);
    Route::get('get-receipt/{id}', [ReceiptController::class, 'show']);
    Route::post('create-receipt', [ReceiptController::class, 'store']);
    Route::put('update-receipt/{id}', [ReceiptController::class, 'update']);
    Route::delete('delete-receipt/{id}', [ReceiptController::class, 'destroy']);
});
