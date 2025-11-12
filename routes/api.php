<?php

//Common controllers
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Common\AddressController;
use App\Http\Controllers\Common\PhoneNumberController;
use App\Http\Controllers\Common\NotificationController;
use App\Http\Controllers\Common\ReferralController;
use App\Http\Controllers\Auth\SocialAuthController;


// Individual controllers
use App\Http\Controllers\Individual\IndividualController;

// Organisation controllers
use App\Http\Controllers\Common\UserCountryController;

use App\Http\Controllers\Ecommerce\Category\BusinessTypeController;
use App\Http\Controllers\Ecommerce\Category\CategoryController;
use App\Http\Controllers\Ecommerce\Category\SubCategoryController;
use App\Http\Controllers\Ecommerce\Category\SubSubCategoryController;
use App\Http\Controllers\Ecommerce\BrandController;
use App\Http\Controllers\Ecommerce\Product\ProductController;
use App\Http\Controllers\Ecommerce\Order\OrderItemController;
use App\Http\Controllers\Ecommerce\Order\OrderController;
use App\Http\Controllers\Ecommerce\Order\OrderDetailController;
use App\Http\Controllers\Org\Accounts\AccountsController;
use App\Http\Controllers\Org\Accounts\AccountsFundController;
use App\Http\Controllers\Org\Asset\AssetController;
use App\Http\Controllers\Org\Asset\AssetLifecycleStatusController;
use App\Http\Controllers\Org\Committee\CommitteeController;
use App\Http\Controllers\Org\Committee\CommitteeMemberController;
use App\Http\Controllers\Org\Event\EventController;
use App\Http\Controllers\Org\Event\EventAttendanceController;
use App\Http\Controllers\Org\Event\EventSummaryController;
use App\Http\Controllers\Org\Event\EventGuestAttendanceController;
use App\Http\Controllers\Org\History\HistoryController;
use App\Http\Controllers\Org\Meeting\MeetingController;
use App\Http\Controllers\Org\Meeting\MeetingMinutesController;
use App\Http\Controllers\Org\Meeting\MeetingAttendanceController;
use App\Http\Controllers\Org\Meeting\MeetingGuestAttendanceController;
use App\Http\Controllers\Org\Membership\MembershipStatusController;
use App\Http\Controllers\Org\Membership\OrgMembershipTypeController;
use App\Http\Controllers\Org\Membership\OrgMembershipRenewalCycleController;
use App\Http\Controllers\Org\Membership\OrgMembershipRenewalPriceController;
use App\Http\Controllers\Org\Membership\OrgMembershipRenewalController;
use App\Http\Controllers\Org\Membership\OrgMemberController;
use App\Http\Controllers\Org\Membership\MembershipTerminationController;
use App\Http\Controllers\Org\Membership\MembershipTerminationReasonController;
use App\Http\Controllers\Org\Membership\FamilyMemberController;
use App\Http\Controllers\Org\Membership\OrgIndependentMemberController;
use App\Http\Controllers\Org\Membership\UnlinkMemberController;
use App\Http\Controllers\Org\OfficeDocument\OfficeDocumentController;
use App\Http\Controllers\Org\Project\ProjectAttendanceController;
use App\Http\Controllers\Org\Project\ProjectSummaryController;
use App\Http\Controllers\Org\Project\ProjectController;
use App\Http\Controllers\Org\Project\ProjectGuestAttendanceController;
use App\Http\Controllers\Org\Recognition\RecognitionController;
use App\Http\Controllers\Org\Report\OrgReportController;
use App\Http\Controllers\Org\StrategicPlan\StrategicPlanController;
use App\Http\Controllers\Org\SuccessStory\SuccessStoryController;
use App\Http\Controllers\Org\YearPlan\YearPlanController;
use App\Http\Controllers\Org\FounderController;
use App\Http\Controllers\Org\OrgAdministratorController;
use App\Http\Controllers\Org\OrgProfileController;

use App\Http\Controllers\SuperAdmin\Financial\ReceiptController;


// Superadmin
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use App\Http\Controllers\SuperAdmin\Settings\AttendanceTypeController;
use App\Http\Controllers\SuperAdmin\Settings\ConductTypeController;
use App\Http\Controllers\SuperAdmin\Settings\CountryController;
use App\Http\Controllers\SuperAdmin\Settings\CountryRegionController;
use App\Http\Controllers\SuperAdmin\Settings\CurrencyController;
use App\Http\Controllers\SuperAdmin\Settings\DesignationController;
use App\Http\Controllers\SuperAdmin\Settings\DialingCodeController;
use App\Http\Controllers\SuperAdmin\Settings\LanguageController;
use App\Http\Controllers\SuperAdmin\Settings\MembershipRenewalCycleController;
use App\Http\Controllers\SuperAdmin\Settings\MembershipTypeController;
use App\Http\Controllers\SuperAdmin\Settings\PrivacySetupController;
use App\Http\Controllers\SuperAdmin\Settings\RegionController;
use App\Http\Controllers\SuperAdmin\Settings\RegionCurrencyController;
use App\Http\Controllers\SuperAdmin\Settings\TimeZoneSetupController;
use App\Http\Controllers\SuperAdmin\Financial\InvoiceController;
use App\Http\Controllers\SuperAdmin\Financial\RegionalTaxRateController;
use App\Http\Controllers\SuperAdmin\Financial\Management\EverydayMemberCountAndBillingController;
use App\Http\Controllers\SuperAdmin\Financial\Management\ManagementAndStorageBillingController;
use App\Http\Controllers\SuperAdmin\Financial\Management\ManagementPricingController;
use App\Http\Controllers\SuperAdmin\Financial\Management\ManagementSubscriptionController;
use App\Http\Controllers\SuperAdmin\Financial\Storage\EverydayStorageBillingController;


use App\Http\Controllers\SuperAdmin\PaymentGateway\StripeController;
use App\Http\Controllers\Auth\ForgotPasswordController;

Route::get('/test', function () {
    return response()->json(['status' => 'Laravel is running']);
});

//Auth
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('/verify-account/{uuid}', [AuthController::class, 'verify']);

Route::post('/oauth/google/complete', [SocialAuthController::class, 'completeProfile'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetCode']);
Route::post('/verify-code', [ForgotPasswordController::class, 'verifyResetCode']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);


// ----------------------- Need to separate only index outside auth --------------------
Route::group(['prefix' => 'countries'], function () {
    Route::get('/', [CountryController::class, 'index']);
    Route::post('/', [CountryController::class, 'store']);
    Route::put('{id}', [CountryController::class, 'update']);
    Route::delete('{id}', [CountryController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::put('update-first-last-name/{userId}', [AuthController::class, 'firstLastNameUpdate']);
    Route::put('update-last-name/{userId}', [AuthController::class, 'lastNameUpdate']);

    Route::put('update-name/{userId}', [AuthController::class, 'nameUpdate']);
    Route::put('update-username/{userId}', [AuthController::class, 'usernameUpdate']);
    Route::put('update-email/{userId}', [AuthController::class, 'userEmailUpdate']);
    Route::post('update-password/{userId}', [AuthController::class, 'updatePassword']);


    // Common for both Individual and Organisation
    // Notifications
    Route::get('/notifications/get-all', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/mark-as-read/{notificationId}', [NotificationController::class, 'markAsRead']);


    // ----------------------- Organisation --------------------

    Route::get('/notifications/get-all/{userId}', [NotificationController::class, 'getNotifications']);
    Route::post('/notifications/mark-all-as-read/{userId}', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/mark-as-read/{userId}/{notificationId}', [NotificationController::class, 'markAsRead']);
    Route::get('/org-profile-data/{userId}', [OrgProfileController::class, 'index']);
    Route::put('/org-profile-update/{userId}', [OrgProfileController::class, 'update']);
    Route::post('/org-profile/logo/{userId}', [OrgProfileController::class, 'updateLogo']);
    Route::get('/org-profile/logo', [OrgProfileController::class, 'getLogo']);


    Route::group(['prefix' => 'addresses'], function () {
        Route::get('/', [AddressController::class, 'index']);
        Route::post('/', [AddressController::class, 'store']);
        Route::put('/{id}', [AddressController::class, 'update']);
    });
    Route::group(['prefix' => 'phone-numbers'], function () {
        Route::get('/', [PhoneNumberController::class, 'index']);
        Route::get('/{id}', [PhoneNumberController::class, 'show']);
        Route::post('/', [PhoneNumberController::class, 'store']);
        Route::put('/{id}', [PhoneNumberController::class, 'update']);

        // Route::get('/dialing-codes', [PhoneNumberController::class, 'getAllDialingCodes']);
    });


    Route::get('org-all-bill', [ManagementAndStorageBillingController::class, 'orgAllBill']);

    Route::get('/referrals', [ReferralController::class, 'index']);
    Route::get('/referrals/stats', [ReferralController::class, 'stats']);


    //Org finance related api
    Route::group(['prefix' => 'org-financial'], function () {
        Route::get('/sub-month-bill-calculation', [EverydayMemberCountAndBillingController::class, 'subMonthBillCalculation']);
        Route::get('/current-month-bill-calculation', [EverydayMemberCountAndBillingController::class, 'currentMonthBillCalculation']);
    });
    Route::group(['prefix' => 'transactions'], function () {
        Route::get('/', [AccountsController::class, 'index']);
        Route::post('/', [AccountsController::class, 'store']);
        Route::put('/{id}', [AccountsController::class, 'update']);
        Route::delete('/{id}', [AccountsController::class, 'destroy']);
    });
    Route::group(['prefix' => 'funds'], function () {
        Route::get('/', [AccountsFundController::class, 'index']);
        Route::post('/', [AccountsFundController::class, 'store']);
        Route::put('/{id}', [AccountsFundController::class, 'update']);
        Route::delete('/{id}', [AccountsFundController::class, 'destroy']);
    });
    Route::group(['prefix' => 'accounts-transaction-currencies'], function () {
        Route::get('/', [AccountsController::class, 'getAccountsTransactionCurrency']);
        Route::post('/', [AccountsController::class, 'storeAccountsTransactionCurrency']);
        Route::put('/{id}', [AccountsController::class, 'updateAccountsTransactionCurrency']);
    });
    Route::group(['prefix' => 'histories'], function () {
        Route::get('/', [HistoryController::class, 'index']);
        Route::get('/{id}', [HistoryController::class, 'show']);
        Route::post('/', [HistoryController::class, 'store']);
        Route::post('/{id}', [HistoryController::class, 'update']);
        Route::delete('/{id}', [HistoryController::class, 'destroy']);
    });
    Route::group(['prefix' => 'year-plans'], function () {
        Route::get('/', [YearPlanController::class, 'index']);
        Route::get('/{id}', [YearPlanController::class, 'show']);
        Route::post('/', [YearPlanController::class, 'store']);
        Route::post('/{id}', [YearPlanController::class, 'update']);
        Route::delete('/{id}', [YearPlanController::class, 'destroy']);
    });
    Route::group(['prefix' => 'recognitions'], function () {
        Route::get('/', [RecognitionController::class, 'index']);
        Route::get('/{id}', [RecognitionController::class, 'show']);
        Route::post('/', [RecognitionController::class, 'store']);
        Route::post('/{id}', [RecognitionController::class, 'update']);
        Route::delete('/{id}', [RecognitionController::class, 'destroy']);
    });
    Route::group(['prefix' => 'strategic-plans'], function () {
        Route::get('/', [StrategicPlanController::class, 'index']);
        Route::get('/{id}', [StrategicPlanController::class, 'show']);
        Route::post('/', [StrategicPlanController::class, 'store']);
        Route::post('/{id}', [StrategicPlanController::class, 'update']);
        Route::delete('/{id}', [StrategicPlanController::class, 'destroy']);
    });
    Route::group(['prefix' => 'success-stories'], function () {
        Route::get('/', [SuccessStoryController::class, 'index']);
        Route::get('/{id}', [SuccessStoryController::class, 'show']);
        Route::post('/', [SuccessStoryController::class, 'store']);
        Route::post('/{id}', [SuccessStoryController::class, 'update']);
        Route::delete('/{id}', [SuccessStoryController::class, 'destroy']);
    });

    Route::group(['prefix' => 'office-documents'], function () {
        Route::get('/', [OfficeDocumentController::class, 'index']);
        Route::get('/{id}', [OfficeDocumentController::class, 'show']);
        Route::post('/', [OfficeDocumentController::class, 'store']);
        Route::put('/{id}', [OfficeDocumentController::class, 'update']);
        Route::delete('/{id}', [OfficeDocumentController::class, 'destroy']);
    });

    Route::group(['prefix' => 'membership-termination-reasons'], function () {
        Route::get('/', [MembershipTerminationReasonController::class, 'index']);
        Route::post('/', [MembershipTerminationReasonController::class, 'store']);
        Route::put('/{id}', [MembershipTerminationReasonController::class, 'update']);
        Route::delete('/{id}', [MembershipTerminationReasonController::class, 'destroy']);
    });

    Route::group(['prefix' => 'membership-terminations'], function () {
        Route::get('/', [MembershipTerminationController::class, 'index']);
        Route::get('/{id}', [MembershipTerminationController::class, 'show']);
        Route::post('/', [MembershipTerminationController::class, 'store']);
        Route::put('/{id}', [MembershipTerminationController::class, 'update']);
        Route::delete('/{id}', [MembershipTerminationController::class, 'destroy']);
    });

    Route::group(['prefix' => 'membership-statuses'], function () {
        Route::get('/', [MembershipStatusController::class, 'index']);
        Route::get('/{id}', [MembershipStatusController::class, 'show']);
        Route::post('/', [MembershipStatusController::class, 'store']);
        Route::put('/{id}', [MembershipStatusController::class, 'update']);
        Route::delete('/{id}', [MembershipStatusController::class, 'destroy']);
    });

    Route::group(['prefix' => 'org-membership-types'], function () {
        Route::get('/', [OrgMembershipTypeController::class, 'index']);
        Route::get('/{id}', [OrgMembershipTypeController::class, 'show']);
        Route::post('/', [OrgMembershipTypeController::class, 'store']);
        Route::put('/{id}', [OrgMembershipTypeController::class, 'update']);
        Route::delete('/{id}', [OrgMembershipTypeController::class, 'destroy']);
    });

    Route::group(['prefix' => 'org-membership-renewal-cycles'], function () {
         Route::get('/', [OrgMembershipRenewalCycleController::class, 'index']);
        Route::post('/', [OrgMembershipRenewalCycleController::class, 'store']);
        Route::get('/{id}', [OrgMembershipRenewalCycleController::class, 'show']);
        Route::put('/{id}', [OrgMembershipRenewalCycleController::class, 'update']);
        Route::delete('/{id}', [OrgMembershipRenewalCycleController::class, 'destroy']);
    });

    Route::group(['prefix' => 'org-membership-renewal-prices'], function () {
         Route::get('/', [OrgMembershipRenewalPriceController::class, 'index']);
        Route::post('/', [OrgMembershipRenewalPriceController::class, 'store']);
        Route::get('/{id}', [OrgMembershipRenewalPriceController::class, 'show']);
        Route::put('/{id}', [OrgMembershipRenewalPriceController::class, 'update']);
        Route::delete('/{id}', [OrgMembershipRenewalPriceController::class, 'destroy']);
    });

    Route::group(['prefix' => 'org-membership-renewals'], function () {
         Route::get('/', [OrgMembershipRenewalController::class, 'index']);
        Route::post('/', [OrgMembershipRenewalController::class, 'store']);
        Route::get('/{id}', [OrgMembershipRenewalController::class, 'show']);
        Route::put('/{id}', [OrgMembershipRenewalController::class, 'update']);
        Route::delete('/{id}', [OrgMembershipRenewalController::class, 'destroy']);
    });

    Route::group(['prefix' => 'org-members'], function () {
        Route::get('/', [OrgMemberController::class, 'index']);
        Route::get('/list/{userId}', [OrgMemberController::class, 'getMemberList']);
        Route::post('/search', [OrgMemberController::class, 'search']);
        Route::post('/create', [OrgMemberController::class, 'store']);
        Route::get('/{id}', [OrgMemberController::class, 'show']);
        Route::post('/check', [OrgMemberController::class, 'checkMember']);
        Route::put('/{id}', [OrgMemberController::class, 'update']);
        Route::delete('/{id}', [OrgMemberController::class, 'destroy']);
    });
    Route::get('/this-year-new-member-count', [OrgMemberController::class, 'thisYearNewMemberCount']);
    // Route::get('/org-terminated-members', [OrgMemberController::class, 'getOrgTerminatedMembers']);
    Route::get('/org-terminated-members', [MembershipTerminationController::class, 'getOrgTerminatedMembers']);
    Route::get('/org-all-member-name', [OrgMemberController::class, 'getOrgAllMemberName']);
    Route::get('/total-org-member-count', [OrgMemberController::class, 'totalOrgMemberCount']);

    Route::group(['prefix' => 'family-members'], function () {
        Route::get('/', [FamilyMemberController::class, 'index']);
        Route::post('/', [FamilyMemberController::class, 'store']);
        Route::get('{id}', [FamilyMemberController::class, 'show']);
        Route::put('{id}', [FamilyMemberController::class, 'update']);
        Route::delete('{id}', [FamilyMemberController::class, 'destroy']);
    });


    Route::group(['prefix' => 'independent-members'], function () {
        Route::get('/', [OrgIndependentMemberController::class, 'index']);
        Route::get('/{id}', [OrgIndependentMemberController::class, 'show']);
        Route::post('/', [OrgIndependentMemberController::class, 'store']);
        Route::put('/{id}', [OrgIndependentMemberController::class, 'update']);
        Route::delete('/{id}', [OrgIndependentMemberController::class, 'destroy']);
    });
    Route::group(['prefix' => 'unlink-members'], function () {
        Route::get('/', [UnlinkMemberController::class, 'index']);
        Route::get('/{id}', [UnlinkMemberController::class, 'show']);
        Route::post('/', [UnlinkMemberController::class, 'store']);
        Route::put('/{id}', [UnlinkMemberController::class, 'update']);
        Route::delete('/{id}', [UnlinkMemberController::class, 'destroy']);
    });
    Route::group(['prefix' => 'org-administrators'], function () {
        Route::post('/org-administrators/check', [OrgAdministratorController::class, 'checkAdministratorExists']);
        Route::get('/primary', [OrgAdministratorController::class, 'getPrimaryAdministrator']);
        Route::get('/', [OrgAdministratorController::class, 'index']);
        Route::post('/', [OrgAdministratorController::class, 'store']);
        Route::put('/{id}', [OrgAdministratorController::class, 'update']);
        Route::delete('/{id}', [OrgAdministratorController::class, 'destroy']);
    });
    Route::group(['prefix' => 'committees'], function () {
        Route::get('/', [CommitteeController::class, 'index']);
        Route::get('/{id}', [CommitteeController::class, 'show']);
        Route::post('/', [CommitteeController::class, 'store']);
        Route::put('/{id}', [CommitteeController::class, 'update']);
        Route::delete('/{id}', [CommitteeController::class, 'destroy']);
    });
    Route::group(['prefix' => 'committee-members'], function () {
        Route::get('/{id}', [CommitteeMemberController::class, 'index']);
        // Route::get('/{id}', [CommitteeMemberController::class, 'show']);
        Route::post('/', [CommitteeMemberController::class, 'store']);
        Route::put('/{id}', [CommitteeMemberController::class, 'update']);
        Route::delete('/{id}', [CommitteeMemberController::class, 'destroy']);
    });
    Route::group(['prefix' => 'meetings'], function () {
        Route::get('/', [MeetingController::class, 'index']);
        Route::get('/{id}', [MeetingController::class, 'show']);
        Route::post('/', [MeetingController::class, 'store']);
        Route::post('/{id}', [MeetingController::class, 'update']);
        Route::delete('/{id}', [MeetingController::class, 'destroy']);
    });
    Route::get('/org-next-meeting', [MeetingController::class, 'orgNextMeeting']);


    Route::group(['prefix' => 'meeting-minutes'], function () {
        Route::get('/', [MeetingMinutesController::class, 'index']);
        Route::get('/{id}', [MeetingMinutesController::class, 'show']);
        Route::post('/', [MeetingMinutesController::class, 'store']);
        Route::post('/{id}', [MeetingMinutesController::class, 'update']);
        Route::delete('/{id}', [MeetingMinutesController::class, 'destroy']);
    });
    Route::group(['prefix' => 'meeting-attendances'], function () {
        Route::get('/', [MeetingAttendanceController::class, 'index']);
        Route::get('/{id}', [MeetingAttendanceController::class, 'show']);
        Route::post('/', [MeetingAttendanceController::class, 'store']);
        Route::put('/{id}', [MeetingAttendanceController::class, 'update']);
        Route::delete('/{id}', [MeetingAttendanceController::class, 'destroy']);
    });
    Route::group(['prefix' => 'meeting-guest-attendances'], function () {
        Route::get('/', [MeetingGuestAttendanceController::class, 'index']);
        Route::get('/{id}', [MeetingGuestAttendanceController::class, 'show']);
        Route::post('/', [MeetingGuestAttendanceController::class, 'store']);
        Route::put('/{id}', [MeetingGuestAttendanceController::class, 'update']);
        Route::delete('/{id}', [MeetingGuestAttendanceController::class, 'destroy']);
    });
    Route::group(['prefix' => 'events'], function () {
        Route::get('/event/{eventId}', [EventController::class, 'getEvent']);
        Route::get('/', [EventController::class, 'index']);
        Route::post('/', [EventController::class, 'store']);
        Route::post('/{eventId}', [EventController::class, 'update']);
        Route::delete('/{eventId}', [EventController::class, 'destroy']);
    });
    Route::group(['prefix' => 'event-attendances'], function () {
        Route::get('/', [EventAttendanceController::class, 'index']);
        Route::get('/{id}', [EventAttendanceController::class, 'show']);
        Route::post('/', [EventAttendanceController::class, 'store']);
        Route::put('/{id}', [EventAttendanceController::class, 'update']);
        Route::delete('/{id}', [EventAttendanceController::class, 'destroy']);
    });
    Route::group(['prefix' => 'event-guest-attendances'], function () {
        Route::get('/', [EventGuestAttendanceController::class, 'index']);
        Route::get('/{id}', [EventGuestAttendanceController::class, 'show']);
        Route::post('/', [EventGuestAttendanceController::class, 'store']);
        Route::put('/{id}', [EventGuestAttendanceController::class, 'update']);
        Route::delete('/{id}', [EventGuestAttendanceController::class, 'destroy']);
    });
    Route::group(['prefix' => 'event-summaries'], function () {
        Route::get('/', [EventSummaryController::class, 'index']);
        Route::get('/{id}', [EventSummaryController::class, 'show']);
        Route::post('/', [EventSummaryController::class, 'store']);
        Route::post('/{id}', [EventSummaryController::class, 'update']);
        Route::delete('/{id}', [EventSummaryController::class, 'destroy']);
    });
    Route::group(['prefix' => 'projects'], function () {
        Route::get('/', [ProjectController::class, 'index']);
        Route::get('/{projectId}', [ProjectController::class, 'show']);
        Route::post('/', [ProjectController::class, 'store']);
        Route::post('/{userId}', [ProjectController::class, 'update']);
        Route::delete('/{id}', [ProjectController::class, 'destroy']);
    });
    Route::group(['prefix' => 'project-attendances'], function () {
        Route::get('/', [ProjectAttendanceController::class, 'index']);
        Route::get('/{id}', [ProjectAttendanceController::class, 'show']);
        Route::post('/', [ProjectAttendanceController::class, 'store']);
        Route::put('/{id}', [ProjectAttendanceController::class, 'update']);
        Route::delete('/{id}', [ProjectAttendanceController::class, 'destroy']);
    });
    Route::group(['prefix' => 'project-guest-attendances'], function () {
        Route::get('/', [ProjectGuestAttendanceController::class, 'index']);
        Route::get('/{id}', [ProjectGuestAttendanceController::class, 'show']);
        Route::post('/', [ProjectGuestAttendanceController::class, 'store']);
        Route::put('/{id}', [ProjectGuestAttendanceController::class, 'update']);
        Route::delete('/{id}', [ProjectGuestAttendanceController::class, 'destroy']);
    });
    Route::group(['prefix' => 'project-summaries'], function () {
        Route::get('/', [ProjectSummaryController::class, 'index']);
        Route::get('/{id}', [ProjectSummaryController::class, 'show']);
        Route::post('/', [ProjectSummaryController::class, 'store']);
        Route::post('/{id}', [ProjectSummaryController::class, 'update']);
        Route::delete('/{id}', [ProjectSummaryController::class, 'destroy']);
    });
    Route::group(['prefix' => 'founders'], function () {
        Route::get('/', [FounderController::class, 'index']);
        Route::post('/', [FounderController::class, 'store']);
        Route::post('/{id}', [FounderController::class, 'update']);
        Route::delete('/{id}', [FounderController::class, 'destroy']);
    });
    Route::group(['prefix' => 'assets'], function () {
        Route::get('/{assetId}', [AssetController::class, 'getAssetDetails']);
        Route::get('/', [AssetController::class, 'index']);
        Route::post('/', [AssetController::class, 'store']);
        Route::post('/{id}', [AssetController::class, 'update']);
        Route::delete('/{id}', [AssetController::class, 'destroy']);
    });
    Route::group(['prefix' => 'privacy-setups'], function () {
        Route::get('/', [PrivacySetupController::class, 'index']);
        Route::post('/', [PrivacySetupController::class, 'store']);
        Route::put('{id}', [PrivacySetupController::class, 'update']);
        Route::delete('{id}', [PrivacySetupController::class, 'destroy']);
        Route::get('/all', [PrivacySetupController::class, 'getAllPrivacySetupForSuperAdmin']);
    });
    Route::get('asset-lifecycle-setups', [AssetLifecycleStatusController::class, 'index']);

    Route::group(['prefix' => 'management-subscriptions'], function () {
        Route::get('/', [ManagementSubscriptionController::class, 'index']);
        Route::post('/', [ManagementSubscriptionController::class, 'store']);
        Route::put('{id}', [ManagementSubscriptionController::class, 'update']);
        Route::delete('{id}', [ManagementSubscriptionController::class, 'destroy']);
        Route::get('/daily-price-rate', [ManagementSubscriptionController::class, 'managementPriceRate']);
        Route::get('/currencies', [ManagementSubscriptionController::class, 'currency']);
    });

    Route::group(['prefix' => 'invoices'], function () {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::get('{id}', [InvoiceController::class, 'show']);
        Route::post('/', [InvoiceController::class, 'store']);
        Route::put('{id}', [InvoiceController::class, 'update']);
        Route::delete('{id}', [InvoiceController::class, 'destroy']);
        Route::get('/all', [InvoiceController::class, 'indexForSuperadmin']);
    });

    Route::group(['prefix' => 'receipts'], function () {
        Route::get('/org-receipts', [ReceiptController::class, 'orgIndex']);
    });

    // Route::group(['prefix' => 'stripe'], function () {
    //     Route::post('/create-checkout-session', [StripeController::class, 'createCheckoutSession']);
    //     Route::post('/webhook', [StripeController::class, 'handleWebhook']);
    //     Route::get('/checkout-success', [StripeController::class, 'checkoutSuccess']);
    //     Route::get('/checkout-cancel', [StripeController::class, 'checkoutCancel']);
    // });

    Route::group(['prefix' => 'reports'], function () {
        Route::get('/membership-growth', [OrgReportController::class, 'getMembershipGrowthReport']);
    });
    Route::get('/reports', [OrgReportController::class, 'getIncomeReport']);
    Route::get('/org-expense-reports', [OrgReportController::class, 'getExpenseReport']);






    // ----------------------- Individual --------------------
    Route::get('/individual_profile_data/{userId}', [IndividualController::class, 'getProfileImage']);
    Route::get('/profileimage/{userId}', [IndividualController::class, 'getProfileImage']);
    Route::post('/profileimage/{userId}', [IndividualController::class, 'updateProfileImage']);
    Route::get('/individual-users', [IndividualController::class, 'getIndividualUser']);
    // Route::get('/connected-org-list/{userId}', [IndividualController::class, 'getOrganisationByIndividualId']);
    Route::get('/connected-org-list', [IndividualController::class, 'getOrganisationByIndividualId']);

    Route::middleware('auth:sanctum')->get('/individual/dashboard-summary', [IndividualController::class, 'summary']);
    Route::middleware('auth:sanctum')->get('/individual/meetings', [IndividualController::class, 'meetings']);
    Route::middleware('auth:sanctum')->get('/individual/past_meetings', [IndividualController::class, 'past_meetings']);
    Route::middleware('auth:sanctum')->get('/individual/events', [IndividualController::class, 'past_events']);
    Route::middleware('auth:sanctum')->get('/individual/past_events', [IndividualController::class, 'events']);
    Route::middleware('auth:sanctum')->get('/individual/committees', [IndividualController::class, 'committees']);
    Route::middleware('auth:sanctum')->get('/individual/past_committees', [IndividualController::class, 'past_committees']);
    Route::middleware('auth:sanctum')->get('/individual/projects', [IndividualController::class, 'projects']);
    Route::middleware('auth:sanctum')->get('/individual/past_projects', [IndividualController::class, 'past_projects']);
    Route::middleware('auth:sanctum')->get('/individual/assets', [IndividualController::class, 'assets']);
    Route::middleware('auth:sanctum')->get('/individual/past_assets', [IndividualController::class, 'past_assets']);
    Route::middleware('auth:sanctum')->get('/individual/attendance', [IndividualController::class, 'attendance']);

    // ----------------------- Superadmin --------------------
    Route::get('/super_admin_profile_image/{userId}', [SuperAdminController::class, 'getSuperAdminProfileImage']);
    Route::post('/super_admin_profile_image/{userId}', [SuperAdminController::class, 'updateSuperAdminProfileImage']);
    Route::get('/super_admin_user_data/{id}', [SuperAdminController::class, 'show']);
    Route::get('/management-pricings', [ManagementPricingController::class, 'getUserPriceRate']);


    Route::group(['prefix' => 'management-and-storage-billings'], function () {
        Route::get('/', [ManagementAndStorageBillingController::class, 'index']);
        Route::get('{id}', [ManagementAndStorageBillingController::class, 'show']);
        Route::post('/', [ManagementAndStorageBillingController::class, 'store']);
        Route::put('{id}', [ManagementAndStorageBillingController::class, 'update']);
        Route::delete('{id}', [ManagementAndStorageBillingController::class, 'destroy']);
        Route::get('/superadmin', [ManagementAndStorageBillingController::class, 'indexSuperAdmin']);
        Route::post('/system', [ManagementAndStorageBillingController::class, 'storeBySystem']);
    });
    Route::group(['prefix' => 'every-day-member-count-and-billings'], function () {
        Route::get('/', [EverydayMemberCountAndBillingController::class, 'index']);
        Route::get('{id}', [EverydayMemberCountAndBillingController::class, 'show']);
        Route::post('/', [EverydayMemberCountAndBillingController::class, 'superAdminStore']);
        Route::put('{id}', [EverydayMemberCountAndBillingController::class, 'update']);
        Route::delete('{id}', [EverydayMemberCountAndBillingController::class, 'destroy']);
    });
    Route::group(['prefix' => 'every-day-storage-billings'], function () {
        Route::get('/', [EverydayStorageBillingController::class, 'index']);
        Route::get('{id}', [EverydayStorageBillingController::class, 'show']);
        Route::post('/', [EverydayStorageBillingController::class, 'superAdminStore']);
        Route::put('{id}', [EverydayStorageBillingController::class, 'update']);
        Route::delete('{id}', [EverydayStorageBillingController::class, 'destroy']);
    });

    Route::group(['prefix' => 'management-pricings'], function () {
        Route::get('/', [ManagementPricingController::class, 'index']);
        Route::put('/update', [ManagementPricingController::class, 'update']);
        Route::get('/all-user-price-rate', [ManagementPricingController::class, 'getAllUserPriceRate']);
    });
    Route::group(['prefix' => 'currencies'], function () {
        Route::get('/', [CurrencyController::class, 'index']);
        Route::post('/', [CurrencyController::class, 'store']);
        Route::put('{id}', [CurrencyController::class, 'update']);
        Route::delete('{id}', [CurrencyController::class, 'destroy']);
    });
    Route::group(['prefix' => 'regional-tax-rates'], function () {
        Route::get('/', [RegionalTaxRateController::class, 'index']);
        Route::get('{id}', [RegionalTaxRateController::class, 'show']);
        Route::post('/', [RegionalTaxRateController::class, 'store']);
        Route::put('{id}', [RegionalTaxRateController::class, 'update']);
        Route::delete('{id}', [RegionalTaxRateController::class, 'destroy']);
    });

    Route::group(['prefix' => 'dialing-codes'], function () {
        Route::get('/', [DialingCodeController::class, 'index']);
        Route::post('/', [DialingCodeController::class, 'store']);
        Route::put('/{id}', [DialingCodeController::class, 'update']);
        Route::delete('/{id}', [DialingCodeController::class, 'destroy']);
    });
    Route::group(['prefix' => 'conduct-types'], function () {
        Route::get('/', [ConductTypeController::class, 'index']);
        Route::post('/', [ConductTypeController::class, 'store']);
        Route::put('/{id}', [ConductTypeController::class, 'update']);
        Route::delete('/{id}', [ConductTypeController::class, 'destroy']);
    });
    Route::group(['prefix' => 'attendance-types'], function () {
        Route::get('/', [AttendanceTypeController::class, 'index']);
        Route::post('/', [AttendanceTypeController::class, 'store']);
        Route::put('/{id}', [AttendanceTypeController::class, 'update']);
        Route::delete('/{id}', [AttendanceTypeController::class, 'destroy']);
    });
    Route::group(['prefix' => 'membership-types'], function () {
        Route::get('/', [MembershipTypeController::class, 'index']);
        Route::post('/', [MembershipTypeController::class, 'store']);
        Route::put('/{id}', [MembershipTypeController::class, 'update']);
        Route::delete('/{id}', [MembershipTypeController::class, 'destroy']);
    });

    Route::group(['prefix' => 'membership-renewal-cycles'], function () {
        Route::get('/', [MembershipRenewalCycleController::class, 'index']);
        Route::post('/', [MembershipRenewalCycleController::class, 'store']);
        Route::put('/{id}', [MembershipRenewalCycleController::class, 'update']);
        Route::delete('/{id}', [MembershipRenewalCycleController::class, 'destroy']);
    });
    Route::group(['prefix' => 'designations'], function () {
        Route::get('/', [DesignationController::class, 'index']);
        Route::post('/', [DesignationController::class, 'store']);
        Route::put('/{id}', [DesignationController::class, 'update']);
        Route::delete('/{id}', [DesignationController::class, 'destroy']);
    });
    Route::group(['prefix' => 'languages'], function () {
        Route::get('/', [LanguageController::class, 'index']);
        Route::post('/', [LanguageController::class, 'store']);
        Route::put('/{id}', [LanguageController::class, 'update']);
        Route::delete('/{id}', [LanguageController::class, 'destroy']);
    });
    Route::group(['prefix' => 'time-zone-setups'], function () {
        Route::get('/', [TimeZoneSetupController::class, 'index']);
        Route::post('/', [TimeZoneSetupController::class, 'store']);
        Route::put('/{id}', [TimeZoneSetupController::class, 'update']);
        Route::delete('/{id}', [TimeZoneSetupController::class, 'destroy']);
    });
    Route::get('/get-user-list', [UserCountryController::class, 'getUser']);

    Route::group(['prefix' => 'user-countries'], function () {
        Route::get('/country-name', [OrgProfileController::class, 'getOrgCountry']);
        Route::get('/', [UserCountryController::class, 'index']);
        Route::get('/{id}', [UserCountryController::class, 'show']);
        Route::post('/', [UserCountryController::class, 'store']);
        Route::put('/{id}', [UserCountryController::class, 'update']);
        Route::delete('/{id}', [UserCountryController::class, 'destroy']);
    });
    Route::group(['prefix' => 'regions'], function () {
        Route::get('/', [RegionController::class, 'index']);
        Route::get('/{id}', [RegionController::class, 'show']);
        Route::post('/', [RegionController::class, 'store']);
        Route::put('/{id}', [RegionController::class, 'update']);
        Route::delete('/{id}', [RegionController::class, 'destroy']);
    });
    Route::group(['prefix' => 'country-regions'], function () {
        Route::get('/', [CountryRegionController::class, 'index']);
        Route::get('/{id}', [CountryRegionController::class, 'show']);
        Route::post('/', [CountryRegionController::class, 'store']);
        Route::put('/{id}', [CountryRegionController::class, 'update']);
        Route::delete('/{id}', [CountryRegionController::class, 'destroy']);
    });
    Route::group(['prefix' => 'region-currencies'], function () {
        Route::get('/', [RegionCurrencyController::class, 'index']);
        Route::get('/{id}', [RegionCurrencyController::class, 'show']);
        Route::post('/', [RegionCurrencyController::class, 'store']);
        Route::put('/{id}', [RegionCurrencyController::class, 'update']);
        Route::delete('/{id}', [RegionCurrencyController::class, 'destroy']);
    });
    Route::group(['prefix' => 'business-types'], function () {
        Route::get('/', [BusinessTypeController::class, 'index']);
        Route::get('/{id}', [BusinessTypeController::class, 'show']);
        Route::post('/', [BusinessTypeController::class, 'store']);
        Route::put('/{id}', [BusinessTypeController::class, 'update']);
        Route::delete('/{id}', [BusinessTypeController::class, 'destroy']);
    });
    Route::group(['prefix' => 'categories'], function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
    });
    Route::group(['prefix' => 'sub-categories'], function () {
        Route::get('/', [SubCategoryController::class, 'index']);
        Route::get('/{id}', [SubCategoryController::class, 'show']);
        Route::post('/', [SubCategoryController::class, 'store']);
        Route::put('/{id}', [SubCategoryController::class, 'update']);
        Route::delete('/{id}', [SubCategoryController::class, 'destroy']);
    });
    Route::group(['prefix' => 'sub-sub-categories'], function () {
        Route::get('/', [SubSubCategoryController::class, 'index']);
        Route::get('/{id}', [SubSubCategoryController::class, 'show']);
        Route::post('/', [SubSubCategoryController::class, 'store']);
        Route::put('/{id}', [SubSubCategoryController::class, 'update']);
        Route::delete('/{id}', [SubSubCategoryController::class, 'destroy']);
    });
    Route::group(['prefix' => 'brands'], function () {
        Route::get('/', [BrandController::class, 'index']);
        Route::get('/{id}', [BrandController::class, 'show']);
        Route::post('/', [BrandController::class, 'store']);
        Route::put('/{id}', [BrandController::class, 'update']);
        Route::delete('/{id}', [BrandController::class, 'destroy']);
    });
    Route::group(['prefix' => 'products'], function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::post('/', [ProductController::class, 'store']);
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);
    });
    Route::group(['prefix' => 'orders'], function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::post('/', [OrderController::class, 'store']);
        Route::put('/{id}', [OrderController::class, 'update']);
        Route::delete('/{id}', [OrderController::class, 'destroy']);
    });
    Route::group(['prefix' => 'order-items'], function () {
        Route::get('/', [OrderItemController::class, 'index']);
        Route::get('/{id}', [OrderItemController::class, 'show']);
        Route::post('/', [OrderItemController::class, 'store']);
        Route::put('/{id}', [OrderItemController::class, 'update']);
        Route::delete('/{id}', [OrderItemController::class, 'destroy']);
    });
    Route::group(['prefix' => 'order-details'], function () {
        Route::get('/', [OrderDetailController::class, 'index']);
        Route::get('/{id}', [OrderDetailController::class, 'show']);
        Route::post('/', [OrderDetailController::class, 'store']);
        Route::put('/{id}', [OrderDetailController::class, 'update']);
        Route::delete('/{id}', [OrderDetailController::class, 'destroy']);
    });

    // Route::group(['prefix' => 'payment-gateway-stripe'], function () {
    //     Route::get('/checkout/{invoiceId}', [StripeController::class, 'stripeCreateCheckoutSession']);
    //     Route::get('/success/{invoiceId}', [StripeController::class, 'stripeSuccess']);
    //     Route::get('/cancel/{invoiceId}', [StripeController::class, 'stripeCancel']);
    //     Route::post('/webhook', [StripeController::class, 'stripeHandleWebhook']);
    // });
});
