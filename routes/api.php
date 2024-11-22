<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Individual\IndividualController;
use App\Http\Controllers\Org\OrgMemberListController;
use App\Http\Controllers\Org\CommitteeController;
use App\Http\Controllers\Org\MeetingController;

use App\Http\Controllers\Org\MeetingMinutesController;
use App\Http\Controllers\Org\MeetingAttendanceController;

use App\Http\Controllers\Org\AddressController;
use App\Http\Controllers\Org\OrgAdministratorController;
use App\Http\Controllers\PhoneNumberController;
use App\Http\Controllers\EventAttendanceController;
use App\Http\Controllers\ProjectAttendanceController;

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
use App\Http\Controllers\AccountFundController;
use App\Http\Controllers\OrgReportController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\PrivacySetupController;
use App\Http\Controllers\AssetLifecycleStatusController;

//Org office record
use App\Http\Controllers\OrgOfficeRecordController;

// Billing
use App\Http\Controllers\PackageController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ActiveMemberCountController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PriceRateController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\UserCurrencyController;
use App\Http\Controllers\UserPriceRateController;


// Master Setting
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DialingCodeController;
use App\Http\Controllers\Org\MeetingConductTypeController;
use App\Http\Controllers\Org\MeetingAttendanceTypeController;
use App\Http\Controllers\AttendanceTypeController;
use App\Http\Controllers\ConductTypeController;
use App\Http\Controllers\Org\MembershipTypeController;
use App\Http\Controllers\Org\DesignationController;
use App\Http\Controllers\LanguageListController;
use App\Http\Controllers\RegionalPricingController;
use App\Http\Controllers\TimeZoneSetupController;
use App\Http\Controllers\UserCountryController;

//API for auth
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('/verify-account/{uuid}', [AuthController::class, 'verify']);


Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);



    // ------------------- Individual----------------------------------------------------------------
    Route::get('/individual_profile_data/{userId}', [IndividualController::class, 'getProfileImage']);
    Route::get('/profileimage/{userId}', [IndividualController::class, 'getProfileImage']);
    Route::post('/profileimage/{userId}', [IndividualController::class, 'updateProfileImage']);
    Route::get('/connected-org-list/{userId}', [IndividualController::class, 'getOrganisationByIndividualId']);


    // ------------------- Organisation----------------------------------------------------------------
    // Accounts
    Route::get('/get-transactions', [OrgAccountController::class, 'getTransactions']);
    Route::post('/create-transaction', [OrgAccountController::class, 'createTransaction']);
    Route::put('/update-transaction/{id}', [OrgAccountController::class, 'updateTransaction']);
    Route::delete('/delete-transaction/{id}', [OrgAccountController::class, 'deleteTransaction']);

    // Funds
    Route::get('/get-funds', [AccountFundController::class, 'index']);
    Route::post('/create-fund', [AccountFundController::class, 'store']);
    Route::put('/update-fund/{id}', [AccountFundController::class, 'update']);
    Route::delete('/delete-fund/{id}', [AccountFundController::class, 'destroy']);

    // OrgHistoryController
    Route::get('/get-org-histories', [OrgHistoryController::class, 'index']);
    Route::post('/create-org-history', [OrgHistoryController::class, 'store']);
    Route::put('/update-org-history/{id}', [OrgHistoryController::class, 'update']);
    Route::delete('/delete-org-history/{id}', [OrgHistoryController::class, 'destroy']);

    //Year plan
    Route::get('/get-year-plans', [YearPlanController::class, 'index']);
    Route::get('/year-plan/{id}', [YearPlanController::class, 'show']);
    Route::post('/create-year-plan', [YearPlanController::class, 'store']);
    Route::put('/update-year-plan/{id}', [YearPlanController::class, 'update']);
    Route::delete('/delete-year-plan/{id}', [YearPlanController::class, 'destroy']);

    // OrgRecognitionController
    Route::get('/get-recognitions', [OrgRecognitionController::class, 'index']);
    Route::post('/create-recognition', [OrgRecognitionController::class, 'store']);
    Route::put('/update-recognition/{id}', [OrgRecognitionController::class, 'update']);
    Route::delete('/delete-recognition/{id}', [OrgRecognitionController::class, 'destroy']);

    // StrategicPlan
    Route::get('/get-strategic-plans', [StrategicPlanController::class, 'index']);
    Route::post('/create-strategic-plan', [StrategicPlanController::class, 'store']);
    Route::put('/update-strategic-plan/{id}', [StrategicPlanController::class, 'update']);
    Route::delete('/delete-strategic-plan/{id}', [StrategicPlanController::class, 'destroy']);

    // SuccessStoryController
    Route::get('/get-records', [SuccessStoryController::class, 'index']);
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
    Route::get('/get-office-records', [OrgOfficeRecordController::class, 'index']);
    Route::get('/get-office-record/{recordId}', [OrgOfficeRecordController::class, 'getOfficeRecord']);
    Route::post('/create-office-record', [OrgOfficeRecordController::class, 'store']);
    Route::put('/update-office-record/{id}', action: [OrgOfficeRecordController::class, 'update']);
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
    Route::get('org-committee-list/{userId}', [CommitteeController::class, 'getCommitteeListByUserId']);
    Route::post('create_committee', [CommitteeController::class, 'store']);
    Route::put('update_committee/{id}', [CommitteeController::class, 'update']);
    Route::delete('org-committee/{id}', [CommitteeController::class, 'destroy']);

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

    //Event  
    Route::get('/get-events/{userId}', [OrgEventController::class, 'index']);
    Route::get('/get-event/{eventId}', [OrgEventController::class, 'getEvent']);
    Route::post('/create-event', [OrgEventController::class, 'createEvent']);
    Route::put('/update-event/{eventId}', [OrgEventController::class, 'updateEvent']);
    Route::delete('/delete-event/{eventId}', [OrgEventController::class, 'deleteEvent']);
    //Meeting EventAttendance
    Route::get('/get-org-user-list', [EventAttendanceController::class, 'getOrgUse']);
    Route::get('/get-event-attendances', [EventAttendanceController::class, 'index']);
    Route::get('/get-event-attendance/{id}', [EventAttendanceController::class, 'show']);
    Route::post('/create-event-attendance', [EventAttendanceController::class, 'store']);
    Route::put('/update-event-attendance/{id}', [EventAttendanceController::class, 'update']);
    Route::delete('/delete-event-attendance/{id}', [EventAttendanceController::class, 'destroy']);
    //Project
    Route::get('org-project-list/{userId}', [OrgProjectController::class, 'index']);
    Route::get('/get-project/{projectId}', [OrgProjectController::class, 'getProject']);
    Route::post('create-project', [OrgProjectController::class, 'store']);
    Route::put('update-project/{userId}', [OrgProjectController::class, 'update']);

    //Meeting ProjectAttendance
    Route::get('/get-org-user-list', [ProjectAttendanceController::class, 'getOrgUse']);
    Route::get('/get-project-attendances', [ProjectAttendanceController::class, 'index']);
    Route::get('/get-project-attendance/{id}', [ProjectAttendanceController::class, 'show']);
    Route::post('/create-project-attendance', [ProjectAttendanceController::class, 'store']);
    Route::put('/update-project-attendance/{id}', [ProjectAttendanceController::class, 'update']);
    Route::delete('/delete-project-attendance/{id}', [ProjectAttendanceController::class, 'destroy']);

    // Founder
    Route::post('create-founder', [FounderController::class, 'store']);
    Route::get('get-founder/{userId}', [FounderController::class, 'index']);
    Route::put('update-founder/{id}', [FounderController::class, 'update']);

    //Asset
    Route::get('/get-assets/{userId}', [AssetController::class, 'getAsset']);
    Route::get('/get-asset/{assetId}', [AssetController::class, 'getAssetDetails']);
    Route::post('/create-asset', [AssetController::class, 'store']);
    Route::put('/update-asset/{id}', [AssetController::class, 'update']);
    Route::delete('/delete-asset/{id}', [AssetController::class, 'destroy']);

    // privacy_setups
    Route::get('privacy-setups', [PrivacySetupController::class, 'index']);
    // asset_lifecycle_setups
    Route::get('asset-lifecycle-setups', [AssetLifecycleStatusController::class, 'index']);

    //Billing
    Route::get('packages', [PackageController::class, 'index']);

    Route::get('subscription', [SubscriptionController::class, 'show']);
    Route::post('subscription', [SubscriptionController::class, 'store']);
    Route::put('subscription/{id}', [SubscriptionController::class, 'update']);
    Route::delete('subscription{id}', [SubscriptionController::class, 'destroy']);
    Route::get('/user-price-rate', [RegionalPricingController::class, 'getUserPriceRate']);

    Route::get('/active-member-counts', [ActiveMemberCountController::class, 'show']);
    Route::get('/previous-month-bill-calculation', [ActiveMemberCountController::class, 'getPreviousMonthBillCalculation']);

    Route::get('/invoices', [InvoiceController::class, 'index']);

    Route::get('/billing-list', [BillingController::class, 'index']);


    // ------------------- SuperAdmin----------------------------------------------------------------
    //API for SuperAdmin
    Route::get('/super_admin_user_data/{id}', [SuperAdminController::class, 'show']);

    //Finance
    //Billing
    Route::get('invoices-for-superadmin', [InvoiceController::class, 'indexForSuperAdmin']);
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
});
