# API route authorisation audit

Generated from `php artisan route:list` and a static check of each controller method (branch `security/tenant-authorization`).

| Category | Routes | Enforced | Review |
|---|---:|---:|---:|
| per-user | 60 | 51 | 1 |
| shared lookup (read) | 22 | 22 | 0 |
| org-scoped | 208 | 197 | 4 |
| superadmin | 144 | 144 | 0 |
| public | 8 | 7 | 1 |

**Categories**: *public* needs no login; *superadmin* is blocked for everyone else by the `superadmin` middleware; *org-scoped* must only touch the current organisation's records; *per-user* must only touch the signed-in person's records; *shared lookup* lists (countries, currencies, types...) are readable by anyone signed in.

**Enforced** is a static check: *yes* means the method uses the organisation/owner helpers or an owner condition; *REVIEW* means no owner condition was found and a person should confirm the route is safe.

| Method | Path | Controller@method | Category | Enforced | Note |
|---|---|---|---|---|---|
| GET | `/api/addresses` | Common\\AddressController@index | per-user | yes |  |
| POST | `/api/addresses` | Common\\AddressController@store | per-user | yes |  |
| PUT | `/api/addresses/{id}` | Common\\AddressController@update | per-user | yes |  |
| GET | `/api/addresses/address-format` | Common\\AddressController@getAddressFormat | per-user | yes |  |
| GET | `/api/asset-lifecycle-setups` | Org\\Asset\\AssetLifecycleStatusController@index | shared lookup (read) | yes |  |
| GET | `/api/assets` | Org\\Asset\\AssetController@index | org-scoped | yes |  |
| POST | `/api/assets` | Org\\Asset\\AssetController@store | org-scoped | yes |  |
| GET | `/api/assets/{assetId}` | Org\\Asset\\AssetController@getAssetDetails | org-scoped | yes |  |
| DELETE | `/api/assets/{id}` | Org\\Asset\\AssetController@destroy | org-scoped | yes |  |
| POST | `/api/assets/{id}` | Org\\Asset\\AssetController@update | org-scoped | yes |  |
| GET | `/api/attendance-types` | SuperAdmin\\Settings\\AttendanceTypeController@index | shared lookup (read) | yes |  |
| POST | `/api/attendance-types` | SuperAdmin\\Settings\\AttendanceTypeController@store | superadmin | yes (middleware) | saves $request->all() |
| DELETE | `/api/attendance-types/{id}` | SuperAdmin\\Settings\\AttendanceTypeController@destroy | superadmin | yes (middleware) |  |
| PUT | `/api/attendance-types/{id}` | SuperAdmin\\Settings\\AttendanceTypeController@update | superadmin | yes (middleware) | saves $request->all() |
| GET | `/api/brands` | Ecommerce\\BrandController@index | superadmin | yes (middleware) |  |
| POST | `/api/brands` | Ecommerce\\BrandController@store | superadmin | yes (middleware) |  |
| DELETE | `/api/brands/{id}` | Ecommerce\\BrandController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/brands/{id}` | Ecommerce\\BrandController@show | superadmin | yes (middleware) |  |
| PUT | `/api/brands/{id}` | Ecommerce\\BrandController@update | superadmin | yes (middleware) |  |
| GET | `/api/business-types` | Ecommerce\\Category\\BusinessTypeController@index | superadmin | yes (middleware) |  |
| POST | `/api/business-types` | Ecommerce\\Category\\BusinessTypeController@store | superadmin | yes (middleware) |  |
| DELETE | `/api/business-types/{id}` | Ecommerce\\Category\\BusinessTypeController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/business-types/{id}` | Ecommerce\\Category\\BusinessTypeController@show | superadmin | yes (middleware) |  |
| PUT | `/api/business-types/{id}` | Ecommerce\\Category\\BusinessTypeController@update | superadmin | yes (middleware) |  |
| GET | `/api/categories` | Ecommerce\\Category\\CategoryController@index | superadmin | yes (middleware) |  |
| POST | `/api/categories` | Ecommerce\\Category\\CategoryController@store | superadmin | yes (middleware) |  |
| DELETE | `/api/categories/{id}` | Ecommerce\\Category\\CategoryController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/categories/{id}` | Ecommerce\\Category\\CategoryController@show | superadmin | yes (middleware) |  |
| PUT | `/api/categories/{id}` | Ecommerce\\Category\\CategoryController@update | superadmin | yes (middleware) |  |
| POST | `/api/committee-members` | Org\\Committee\\CommitteeMemberController@store | org-scoped | yes |  |
| DELETE | `/api/committee-members/{id}` | Org\\Committee\\CommitteeMemberController@destroy | org-scoped | yes |  |
| GET | `/api/committee-members/{id}` | Org\\Committee\\CommitteeMemberController@index | org-scoped | yes |  |
| PUT | `/api/committee-members/{id}` | Org\\Committee\\CommitteeMemberController@update | org-scoped | yes | saves $request->all() |
| GET | `/api/committees` | Org\\Committee\\CommitteeController@index | org-scoped | yes |  |
| POST | `/api/committees` | Org\\Committee\\CommitteeController@store | org-scoped | yes |  |
| DELETE | `/api/committees/{id}` | Org\\Committee\\CommitteeController@destroy | org-scoped | yes |  |
| GET | `/api/committees/{id}` | Org\\Committee\\CommitteeController@show | org-scoped | yes |  |
| PUT | `/api/committees/{id}` | Org\\Committee\\CommitteeController@update | org-scoped | yes |  |
| GET | `/api/conduct-types` | SuperAdmin\\Settings\\ConductTypeController@index | shared lookup (read) | yes |  |
| POST | `/api/conduct-types` | SuperAdmin\\Settings\\ConductTypeController@store | superadmin | yes (middleware) | saves $request->all() |
| DELETE | `/api/conduct-types/{id}` | SuperAdmin\\Settings\\ConductTypeController@destroy | superadmin | yes (middleware) |  |
| PUT | `/api/conduct-types/{id}` | SuperAdmin\\Settings\\ConductTypeController@update | superadmin | yes (middleware) | saves $request->all() |
| GET | `/api/connected-org-list` | Individual\\IndividualController@getOrganisationByIndividualId | per-user | yes |  |
| GET | `/api/countries` | SuperAdmin\\Settings\\CountryController@index | public | yes |  |
| POST | `/api/countries` | SuperAdmin\\Settings\\CountryController@store | superadmin | yes (middleware) | saves $request->all() |
| DELETE | `/api/countries/{id}` | SuperAdmin\\Settings\\CountryController@destroy | superadmin | yes (middleware) |  |
| PUT | `/api/countries/{id}` | SuperAdmin\\Settings\\CountryController@update | superadmin | yes (middleware) | saves $request->all() |
| GET | `/api/country-regions` | SuperAdmin\\Settings\\CountryRegionController@index | shared lookup (read) | yes |  |
| POST | `/api/country-regions` | SuperAdmin\\Settings\\CountryRegionController@store | superadmin | yes (middleware) | saves $request->all() |
| DELETE | `/api/country-regions/{id}` | SuperAdmin\\Settings\\CountryRegionController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/country-regions/{id}` | SuperAdmin\\Settings\\CountryRegionController@show | shared lookup (read) | yes |  |
| PUT | `/api/country-regions/{id}` | SuperAdmin\\Settings\\CountryRegionController@update | superadmin | yes (middleware) | saves $request->all() |
| GET | `/api/country-regions/country/{country_id}` | SuperAdmin\\Settings\\CountryRegionController@countryWiseRegionWithCurrency | shared lookup (read) | yes |  |
| GET | `/api/currencies` | SuperAdmin\\Settings\\CurrencyController@index | shared lookup (read) | yes |  |
| POST | `/api/currencies` | SuperAdmin\\Settings\\CurrencyController@store | superadmin | yes (middleware) | saves $request->all() |
| DELETE | `/api/currencies/{id}` | SuperAdmin\\Settings\\CurrencyController@destroy | superadmin | yes (middleware) |  |
| PUT | `/api/currencies/{id}` | SuperAdmin\\Settings\\CurrencyController@update | superadmin | yes (middleware) | saves $request->all() |
| GET | `/api/designations` | SuperAdmin\\Settings\\DesignationController@index | shared lookup (read) | yes |  |
| POST | `/api/designations` | SuperAdmin\\Settings\\DesignationController@store | superadmin | yes (middleware) | saves $request->all() |
| DELETE | `/api/designations/{id}` | SuperAdmin\\Settings\\DesignationController@destroy | superadmin | yes (middleware) |  |
| PUT | `/api/designations/{id}` | SuperAdmin\\Settings\\DesignationController@update | superadmin | yes (middleware) | saves $request->all() |
| GET | `/api/dialing-codes` | SuperAdmin\\Settings\\DialingCodeController@index | shared lookup (read) | yes |  |
| POST | `/api/dialing-codes` | SuperAdmin\\Settings\\DialingCodeController@store | superadmin | yes (middleware) | saves $request->all() |
| DELETE | `/api/dialing-codes/{id}` | SuperAdmin\\Settings\\DialingCodeController@destroy | superadmin | yes (middleware) |  |
| PUT | `/api/dialing-codes/{id}` | SuperAdmin\\Settings\\DialingCodeController@update | superadmin | yes (middleware) | saves $request->all() |
| GET | `/api/event-attendances` | Org\\Event\\EventAttendanceController@index | org-scoped | yes |  |
| POST | `/api/event-attendances` | Org\\Event\\EventAttendanceController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/event-attendances/{id}` | Org\\Event\\EventAttendanceController@destroy | org-scoped | yes |  |
| GET | `/api/event-attendances/{id}` | Org\\Event\\EventAttendanceController@show | org-scoped | n/a | empty stub |
| PUT | `/api/event-attendances/{id}` | Org\\Event\\EventAttendanceController@update | org-scoped | yes | saves $request->all() |
| GET | `/api/event-guest-attendances` | Org\\Event\\EventGuestAttendanceController@index | org-scoped | yes |  |
| POST | `/api/event-guest-attendances` | Org\\Event\\EventGuestAttendanceController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/event-guest-attendances/{id}` | Org\\Event\\EventGuestAttendanceController@destroy | org-scoped | yes |  |
| GET | `/api/event-guest-attendances/{id}` | Org\\Event\\EventGuestAttendanceController@show | org-scoped | n/a | empty stub |
| PUT | `/api/event-guest-attendances/{id}` | Org\\Event\\EventGuestAttendanceController@update | org-scoped | yes | saves $request->all() |
| GET | `/api/event-summaries` | Org\\Event\\EventSummaryController@index | org-scoped | yes |  |
| POST | `/api/event-summaries` | Org\\Event\\EventSummaryController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/event-summaries/{id}` | Org\\Event\\EventSummaryController@destroy | org-scoped | yes |  |
| GET | `/api/event-summaries/{id}` | Org\\Event\\EventSummaryController@show | org-scoped | yes |  |
| POST | `/api/event-summaries/{id}` | Org\\Event\\EventSummaryController@update | org-scoped | yes | saves $request->all() |
| GET | `/api/events` | Org\\Event\\EventController@index | org-scoped | yes |  |
| POST | `/api/events` | Org\\Event\\EventController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/events/{eventId}` | Org\\Event\\EventController@destroy | org-scoped | yes |  |
| POST | `/api/events/{eventId}` | Org\\Event\\EventController@update | org-scoped | yes | saves $request->all() |
| GET | `/api/events/event/{eventId}` | Org\\Event\\EventController@getEvent | org-scoped | yes |  |
| GET | `/api/every-day-member-count-and-billings` | SuperAdmin\\Financial\\Management\\EverydayMemberCountAndBillingController@index | superadmin | yes (middleware) |  |
| POST | `/api/every-day-member-count-and-billings` | SuperAdmin\\Financial\\Management\\EverydayMemberCountAndBillingController@superAdminStore | superadmin | yes (middleware) | saves $request->all() |
| DELETE | `/api/every-day-member-count-and-billings/{id}` | SuperAdmin\\Financial\\Management\\EverydayMemberCountAndBillingController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/every-day-member-count-and-billings/{id}` | SuperAdmin\\Financial\\Management\\EverydayMemberCountAndBillingController@show | superadmin | yes (middleware) |  |
| PUT | `/api/every-day-member-count-and-billings/{id}` | SuperAdmin\\Financial\\Management\\EverydayMemberCountAndBillingController@update | superadmin | yes (middleware) | saves $request->all() |
| GET | `/api/every-day-storage-billings` | SuperAdmin\\Financial\\Storage\\EverydayStorageBillingController@index | superadmin | yes (middleware) |  |
| POST | `/api/every-day-storage-billings` | SuperAdmin\\Financial\\Storage\\EverydayStorageBillingController@superAdminStore | superadmin | yes (middleware) | saves $request->all() |
| DELETE | `/api/every-day-storage-billings/{id}` | SuperAdmin\\Financial\\Storage\\EverydayStorageBillingController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/every-day-storage-billings/{id}` | SuperAdmin\\Financial\\Storage\\EverydayStorageBillingController@show | superadmin | yes (middleware) |  |
| PUT | `/api/every-day-storage-billings/{id}` | SuperAdmin\\Financial\\Storage\\EverydayStorageBillingController@update | superadmin | yes (middleware) | saves $request->all() |
| GET | `/api/family-members` | Org\\Membership\\FamilyMemberController@index | org-scoped | yes |  |
| POST | `/api/family-members` | Org\\Membership\\FamilyMemberController@store | org-scoped | yes |  |
| DELETE | `/api/family-members/{id}` | Org\\Membership\\FamilyMemberController@destroy | org-scoped | yes |  |
| GET | `/api/family-members/{id}` | Org\\Membership\\FamilyMemberController@show | org-scoped | yes |  |
| PUT | `/api/family-members/{id}` | Org\\Membership\\FamilyMemberController@update | org-scoped | yes |  |
| POST | `/api/forgot-password` | Auth\\ForgotPasswordController@sendResetCode | public | yes | throttled |
| GET | `/api/founders` | Org\\FounderController@index | org-scoped | yes |  |
| POST | `/api/founders` | Org\\FounderController@store | org-scoped | yes |  |
| DELETE | `/api/founders/{id}` | Org\\FounderController@destroy | org-scoped | yes |  |
| POST | `/api/founders/{id}` | Org\\FounderController@update | org-scoped | yes |  |
| GET | `/api/fund-transaction-currencies` | Org\\FundManagement\\FundManagementController@getTransactionCurrency | org-scoped | yes |  |
| POST | `/api/fund-transaction-currencies` | Org\\FundManagement\\FundManagementController@storeTransactionCurrency | org-scoped | yes |  |
| PUT | `/api/fund-transaction-currencies/{id}` | Org\\FundManagement\\FundManagementController@updateTransactionCurrency | org-scoped | yes |  |
| GET | `/api/fund-transactions` | Org\\FundManagement\\FundManagementController@index | org-scoped | yes |  |
| POST | `/api/fund-transactions` | Org\\FundManagement\\FundManagementController@store | org-scoped | yes |  |
| DELETE | `/api/fund-transactions/{id}` | Org\\FundManagement\\FundManagementController@destroy | org-scoped | yes |  |
| PUT | `/api/fund-transactions/{id}` | Org\\FundManagement\\FundManagementController@update | org-scoped | yes |  |
| GET | `/api/funds` | Org\\FundManagement\\FundController@index | org-scoped | yes |  |
| POST | `/api/funds` | Org\\FundManagement\\FundController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/funds/{id}` | Org\\FundManagement\\FundController@destroy | org-scoped | yes |  |
| PUT | `/api/funds/{id}` | Org\\FundManagement\\FundController@update | org-scoped | yes | saves $request->all() |
| GET | `/api/get-user-list` | Common\\UserCountryController@getUser | per-user | yes |  |
| GET | `/api/histories` | Org\\History\\HistoryController@index | org-scoped | yes |  |
| POST | `/api/histories` | Org\\History\\HistoryController@store | org-scoped | yes |  |
| DELETE | `/api/histories/{id}` | Org\\History\\HistoryController@destroy | org-scoped | yes |  |
| GET | `/api/histories/{id}` | Org\\History\\HistoryController@show | org-scoped | yes |  |
| POST | `/api/histories/{id}` | Org\\History\\HistoryController@update | org-scoped | yes |  |
| GET | `/api/independent-members` | Org\\Membership\\OrgIndependentMemberController@index | org-scoped | yes |  |
| POST | `/api/independent-members` | Org\\Membership\\OrgIndependentMemberController@store | org-scoped | yes |  |
| DELETE | `/api/independent-members/{id}` | Org\\Membership\\OrgIndependentMemberController@destroy | org-scoped | yes |  |
| GET | `/api/independent-members/{id}` | Org\\Membership\\OrgIndependentMemberController@show | org-scoped | yes |  |
| PUT | `/api/independent-members/{id}` | Org\\Membership\\OrgIndependentMemberController@update | org-scoped | yes |  |
| GET | `/api/individual_profile_data/{userId}` | Individual\\IndividualController@getProfileImage | per-user | yes |  |
| GET | `/api/individual-users` | Individual\\IndividualController@getIndividualUser | per-user | n/a | method missing/empty |
| GET | `/api/individual/assets` | Individual\\IndividualController@assets | per-user | yes |  |
| GET | `/api/individual/attendance` | Individual\\IndividualController@attendance | per-user | yes |  |
| GET | `/api/individual/committees` | Individual\\IndividualController@committees | per-user | yes |  |
| GET | `/api/individual/dashboard-summary` | Individual\\IndividualController@summary | per-user | yes |  |
| GET | `/api/individual/events` | Individual\\IndividualController@past_events | per-user | yes |  |
| GET | `/api/individual/meetings` | Individual\\IndividualController@meetings | per-user | yes |  |
| GET | `/api/individual/past_assets` | Individual\\IndividualController@past_assets | per-user | yes |  |
| GET | `/api/individual/past_committees` | Individual\\IndividualController@past_committees | per-user | yes |  |
| GET | `/api/individual/past_events` | Individual\\IndividualController@events | per-user | yes |  |
| GET | `/api/individual/past_meetings` | Individual\\IndividualController@past_meetings | per-user | yes |  |
| GET | `/api/individual/past_projects` | Individual\\IndividualController@past_projects | per-user | yes |  |
| GET | `/api/individual/projects` | Individual\\IndividualController@projects | per-user | yes |  |
| GET | `/api/invoices` | SuperAdmin\\Financial\\InvoiceController@index | org-scoped | yes |  |
| POST | `/api/invoices` | SuperAdmin\\Financial\\InvoiceController@store | superadmin | yes (middleware) | saves $request->all() |
| DELETE | `/api/invoices/{id}` | SuperAdmin\\Financial\\InvoiceController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/invoices/{id}` | SuperAdmin\\Financial\\InvoiceController@show | org-scoped | yes |  |
| PUT | `/api/invoices/{id}` | SuperAdmin\\Financial\\InvoiceController@update | superadmin | yes (middleware) | saves $request->all() |
| GET | `/api/invoices/all` | SuperAdmin\\Financial\\InvoiceController@indexForSuperadmin | superadmin | yes (middleware) |  |
| GET | `/api/languages` | SuperAdmin\\Settings\\LanguageController@index | shared lookup (read) | yes |  |
| POST | `/api/languages` | SuperAdmin\\Settings\\LanguageController@store | superadmin | yes (middleware) | saves $request->all() |
| DELETE | `/api/languages/{id}` | SuperAdmin\\Settings\\LanguageController@destroy | superadmin | yes (middleware) |  |
| PUT | `/api/languages/{id}` | SuperAdmin\\Settings\\LanguageController@update | superadmin | yes (middleware) | saves $request->all() |
| POST | `/api/login` | Auth\\AuthController@login | public | yes | throttled |
| POST | `/api/logout` | Auth\\AuthController@logout | per-user | yes |  |
| GET | `/api/management-and-storage-billings` | SuperAdmin\\Financial\\Management\\ManagementAndStorageBillingController@index | org-scoped | yes |  |
| POST | `/api/management-and-storage-billings` | SuperAdmin\\Financial\\Management\\ManagementAndStorageBillingController@store | superadmin | yes (middleware) |  |
| DELETE | `/api/management-and-storage-billings/{id}` | SuperAdmin\\Financial\\Management\\ManagementAndStorageBillingController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/management-and-storage-billings/{id}` | SuperAdmin\\Financial\\Management\\ManagementAndStorageBillingController@show | org-scoped | yes |  |
| PUT | `/api/management-and-storage-billings/{id}` | SuperAdmin\\Financial\\Management\\ManagementAndStorageBillingController@update | superadmin | yes (middleware) |  |
| GET | `/api/management-and-storage-billings/superadmin` | SuperAdmin\\Financial\\Management\\ManagementAndStorageBillingController@indexSuperAdmin | superadmin | yes (middleware) |  |
| POST | `/api/management-and-storage-billings/system` | SuperAdmin\\Financial\\Management\\ManagementAndStorageBillingController@storeBySystem | superadmin | yes (middleware) |  |
| GET | `/api/management-packages` | SuperAdmin\\Financial\\Management\\ManagementPackageController@index | per-user | REVIEW |  |
| POST | `/api/management-packages` | SuperAdmin\\Financial\\Management\\ManagementPackageController@store | superadmin | yes (middleware) |  |
| DELETE | `/api/management-packages/{id}` | SuperAdmin\\Financial\\Management\\ManagementPackageController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/management-packages/{id}` | SuperAdmin\\Financial\\Management\\ManagementPackageController@show | per-user | n/a | empty stub |
| PUT | `/api/management-packages/{id}` | SuperAdmin\\Financial\\Management\\ManagementPackageController@update | superadmin | yes (middleware) |  |
| GET | `/api/management-pricings` | SuperAdmin\\Financial\\Management\\ManagementPricingController@index | superadmin | yes (middleware) |  |
| GET | `/api/management-pricings/all-user-price-rate` | SuperAdmin\\Financial\\Management\\ManagementPricingController@getAllUserPriceRate | superadmin | yes (middleware) |  |
| PUT | `/api/management-pricings/update` | SuperAdmin\\Financial\\Management\\ManagementPricingController@update | superadmin | yes (middleware) |  |
| GET | `/api/management-subscriptions` | SuperAdmin\\Financial\\Management\\ManagementSubscriptionController@index | org-scoped | yes |  |
| POST | `/api/management-subscriptions` | SuperAdmin\\Financial\\Management\\ManagementSubscriptionController@store | superadmin | yes (middleware) |  |
| DELETE | `/api/management-subscriptions/{id}` | SuperAdmin\\Financial\\Management\\ManagementSubscriptionController@destroy | superadmin | yes (middleware) |  |
| PUT | `/api/management-subscriptions/{id}` | SuperAdmin\\Financial\\Management\\ManagementSubscriptionController@update | org-scoped | yes |  |
| GET | `/api/management-subscriptions/currencies` | SuperAdmin\\Financial\\Management\\ManagementSubscriptionController@currency | org-scoped | REVIEW |  |
| GET | `/api/management-subscriptions/daily-price-rate` | SuperAdmin\\Financial\\Management\\ManagementSubscriptionController@managementPriceRate | org-scoped | REVIEW |  |
| GET | `/api/management-subscriptions/management-package-prices` | SuperAdmin\\Financial\\Management\\ManagementSubscriptionController@managementPackagePrices | org-scoped | REVIEW |  |
| GET | `/api/me` | Auth\\AuthController@me | per-user | yes |  |
| GET | `/api/meeting-attendances` | Org\\Meeting\\MeetingAttendanceController@index | org-scoped | yes |  |
| POST | `/api/meeting-attendances` | Org\\Meeting\\MeetingAttendanceController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/meeting-attendances/{id}` | Org\\Meeting\\MeetingAttendanceController@destroy | org-scoped | yes |  |
| GET | `/api/meeting-attendances/{id}` | Org\\Meeting\\MeetingAttendanceController@show | org-scoped | n/a | empty stub |
| PUT | `/api/meeting-attendances/{id}` | Org\\Meeting\\MeetingAttendanceController@update | org-scoped | yes | saves $request->all() |
| POST | `/api/meeting-attendances/bulk` | Org\\Meeting\\MeetingAttendanceController@bulkStore | org-scoped | yes |  |
| GET | `/api/meeting-guest-attendances` | Org\\Meeting\\MeetingGuestAttendanceController@index | org-scoped | yes |  |
| POST | `/api/meeting-guest-attendances` | Org\\Meeting\\MeetingGuestAttendanceController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/meeting-guest-attendances/{id}` | Org\\Meeting\\MeetingGuestAttendanceController@destroy | org-scoped | yes |  |
| GET | `/api/meeting-guest-attendances/{id}` | Org\\Meeting\\MeetingGuestAttendanceController@show | org-scoped | n/a | empty stub |
| PUT | `/api/meeting-guest-attendances/{id}` | Org\\Meeting\\MeetingGuestAttendanceController@update | org-scoped | yes | saves $request->all() |
| GET | `/api/meeting-minutes` | Org\\Meeting\\MeetingMinutesController@index | org-scoped | yes |  |
| POST | `/api/meeting-minutes` | Org\\Meeting\\MeetingMinutesController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/meeting-minutes/{id}` | Org\\Meeting\\MeetingMinutesController@destroy | org-scoped | yes |  |
| GET | `/api/meeting-minutes/{id}` | Org\\Meeting\\MeetingMinutesController@show | org-scoped | yes |  |
| POST | `/api/meeting-minutes/{id}` | Org\\Meeting\\MeetingMinutesController@update | org-scoped | yes | saves $request->all() |
| GET | `/api/meetings` | Org\\Meeting\\MeetingController@index | org-scoped | yes |  |
| POST | `/api/meetings` | Org\\Meeting\\MeetingController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/meetings/{id}` | Org\\Meeting\\MeetingController@destroy | org-scoped | yes |  |
| GET | `/api/meetings/{id}` | Org\\Meeting\\MeetingController@show | org-scoped | yes |  |
| POST | `/api/meetings/{id}` | Org\\Meeting\\MeetingController@update | org-scoped | yes | saves $request->all() |
| GET | `/api/membership-renewal-cycles` | SuperAdmin\\Settings\\MembershipRenewalCycleController@index | shared lookup (read) | yes |  |
| POST | `/api/membership-renewal-cycles` | SuperAdmin\\Settings\\MembershipRenewalCycleController@store | superadmin | yes (middleware) | saves $request->all() |
| DELETE | `/api/membership-renewal-cycles/{id}` | SuperAdmin\\Settings\\MembershipRenewalCycleController@destroy | superadmin | yes (middleware) |  |
| PUT | `/api/membership-renewal-cycles/{id}` | SuperAdmin\\Settings\\MembershipRenewalCycleController@update | superadmin | yes (middleware) | saves $request->all() |
| GET | `/api/membership-statuses` | Org\\Membership\\MembershipStatusController@index | shared lookup (read) | yes |  |
| POST | `/api/membership-statuses` | Org\\Membership\\MembershipStatusController@store | superadmin | yes (middleware) | saves $request->all() |
| DELETE | `/api/membership-statuses/{id}` | Org\\Membership\\MembershipStatusController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/membership-statuses/{id}` | Org\\Membership\\MembershipStatusController@show | shared lookup (read) | yes |  |
| PUT | `/api/membership-statuses/{id}` | Org\\Membership\\MembershipStatusController@update | superadmin | yes (middleware) | saves $request->all() |
| GET | `/api/membership-termination-reasons` | Org\\Membership\\MembershipTerminationReasonController@index | shared lookup (read) | yes |  |
| POST | `/api/membership-termination-reasons` | Org\\Membership\\MembershipTerminationReasonController@store | superadmin | yes (middleware) |  |
| DELETE | `/api/membership-termination-reasons/{id}` | Org\\Membership\\MembershipTerminationReasonController@destroy | superadmin | yes (middleware) |  |
| PUT | `/api/membership-termination-reasons/{id}` | Org\\Membership\\MembershipTerminationReasonController@update | superadmin | yes (middleware) |  |
| GET | `/api/membership-terminations` | Org\\Membership\\MembershipTerminationController@index | org-scoped | yes |  |
| POST | `/api/membership-terminations` | Org\\Membership\\MembershipTerminationController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/membership-terminations/{id}` | Org\\Membership\\MembershipTerminationController@destroy | org-scoped | yes |  |
| GET | `/api/membership-terminations/{id}` | Org\\Membership\\MembershipTerminationController@show | org-scoped | yes |  |
| PUT | `/api/membership-terminations/{id}` | Org\\Membership\\MembershipTerminationController@update | org-scoped | yes | saves $request->all() |
| GET | `/api/membership-types` | SuperAdmin\\Settings\\MembershipTypeController@index | shared lookup (read) | yes |  |
| POST | `/api/membership-types` | SuperAdmin\\Settings\\MembershipTypeController@store | superadmin | yes (middleware) | saves $request->all() |
| DELETE | `/api/membership-types/{id}` | SuperAdmin\\Settings\\MembershipTypeController@destroy | superadmin | yes (middleware) |  |
| PUT | `/api/membership-types/{id}` | SuperAdmin\\Settings\\MembershipTypeController@update | superadmin | yes (middleware) | saves $request->all() |
| GET | `/api/notification-names` | Common\\NotificationNameController@index | shared lookup (read) | yes |  |
| POST | `/api/notification-names` | Common\\NotificationNameController@store | superadmin | yes (middleware) |  |
| DELETE | `/api/notification-names/{id}` | Common\\NotificationNameController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/notification-names/{id}` | Common\\NotificationNameController@show | shared lookup (read) | yes |  |
| PUT | `/api/notification-names/{id}` | Common\\NotificationNameController@update | superadmin | yes (middleware) |  |
| GET | `/api/notifications/get-all` | Common\\NotificationController@index | per-user | yes |  |
| GET | `/api/notifications/get-all/{userId}` | Common\\NotificationController@getNotifications | per-user | n/a | method missing/empty |
| POST | `/api/notifications/mark-all-as-read` | Common\\NotificationController@markAllAsRead | per-user | yes |  |
| POST | `/api/notifications/mark-all-as-read/{userId}` | Common\\NotificationController@markAllAsRead | per-user | yes |  |
| POST | `/api/notifications/mark-as-read/{notificationId}` | Common\\NotificationController@markAsRead | per-user | yes |  |
| POST | `/api/notifications/mark-as-read/{userId}/{notificationId}` | Common\\NotificationController@markAsRead | per-user | yes |  |
| POST | `/api/oauth/google/complete` | Auth\\SocialAuthController@completeProfile | public | yes |  |
| GET | `/api/office-documents` | Org\\OfficeDocument\\OfficeDocumentController@index | org-scoped | yes |  |
| POST | `/api/office-documents` | Org\\OfficeDocument\\OfficeDocumentController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/office-documents/{id}` | Org\\OfficeDocument\\OfficeDocumentController@destroy | org-scoped | yes |  |
| GET | `/api/office-documents/{id}` | Org\\OfficeDocument\\OfficeDocumentController@show | org-scoped | yes |  |
| PUT | `/api/office-documents/{id}` | Org\\OfficeDocument\\OfficeDocumentController@update | org-scoped | yes |  |
| GET | `/api/order-details` | Ecommerce\\Order\\OrderDetailController@index | superadmin | yes (middleware) |  |
| POST | `/api/order-details` | Ecommerce\\Order\\OrderDetailController@store | superadmin | yes (middleware) |  |
| DELETE | `/api/order-details/{id}` | Ecommerce\\Order\\OrderDetailController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/order-details/{id}` | Ecommerce\\Order\\OrderDetailController@show | superadmin | yes (middleware) |  |
| PUT | `/api/order-details/{id}` | Ecommerce\\Order\\OrderDetailController@update | superadmin | yes (middleware) |  |
| GET | `/api/order-items` | Ecommerce\\Order\\OrderItemController@index | superadmin | yes (middleware) |  |
| POST | `/api/order-items` | Ecommerce\\Order\\OrderItemController@store | superadmin | yes (middleware) |  |
| DELETE | `/api/order-items/{id}` | Ecommerce\\Order\\OrderItemController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/order-items/{id}` | Ecommerce\\Order\\OrderItemController@show | superadmin | yes (middleware) |  |
| PUT | `/api/order-items/{id}` | Ecommerce\\Order\\OrderItemController@update | superadmin | yes (middleware) |  |
| GET | `/api/orders` | Ecommerce\\Order\\OrderController@index | superadmin | yes (middleware) |  |
| POST | `/api/orders` | Ecommerce\\Order\\OrderController@store | superadmin | yes (middleware) |  |
| DELETE | `/api/orders/{id}` | Ecommerce\\Order\\OrderController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/orders/{id}` | Ecommerce\\Order\\OrderController@show | superadmin | yes (middleware) |  |
| PUT | `/api/orders/{id}` | Ecommerce\\Order\\OrderController@update | superadmin | yes (middleware) |  |
| GET | `/api/org-administrators` | Org\\OrgAdministratorController@index | org-scoped | yes |  |
| POST | `/api/org-administrators` | Org\\OrgAdministratorController@store | org-scoped | yes |  |
| DELETE | `/api/org-administrators/{id}` | Org\\OrgAdministratorController@destroy | org-scoped | yes |  |
| PUT | `/api/org-administrators/{id}` | Org\\OrgAdministratorController@update | org-scoped | yes |  |
| POST | `/api/org-administrators/org-administrators/check` | Org\\OrgAdministratorController@checkAdministratorExists | org-scoped | yes |  |
| GET | `/api/org-administrators/primary` | Org\\OrgAdministratorController@getPrimaryAdministrator | org-scoped | yes |  |
| GET | `/api/org-all-bill` | SuperAdmin\\Financial\\Management\\ManagementAndStorageBillingController@orgAllBill | org-scoped | yes |  |
| GET | `/api/org-all-member-name` | Org\\Membership\\OrgMemberController@getOrgAllMemberName | org-scoped | yes |  |
| GET | `/api/org-expense-reports` | Org\\Report\\OrgReportController@getExpenseReport | org-scoped | yes |  |
| GET | `/api/org-financial/current-month-bill-calculation` | SuperAdmin\\Financial\\Management\\EverydayMemberCountAndBillingController@currentMonthBillCalculation | org-scoped | yes |  |
| GET | `/api/org-financial/sub-month-bill-calculation` | SuperAdmin\\Financial\\Management\\EverydayMemberCountAndBillingController@subMonthBillCalculation | org-scoped | yes |  |
| GET | `/api/org-members` | Org\\Membership\\OrgMemberController@index | org-scoped | yes |  |
| GET | `/api/org-members-users/{orgId}` | Role\\UserRoleController@getOrgMemberList | org-scoped | yes |  |
| DELETE | `/api/org-members/{id}` | Org\\Membership\\OrgMemberController@destroy | org-scoped | yes |  |
| GET | `/api/org-members/{id}` | Org\\Membership\\OrgMemberController@show | org-scoped | yes |  |
| PUT | `/api/org-members/{id}` | Org\\Membership\\OrgMemberController@update | org-scoped | yes |  |
| POST | `/api/org-members/check` | Org\\Membership\\OrgMemberController@checkMember | org-scoped | yes |  |
| POST | `/api/org-members/create` | Org\\Membership\\OrgMemberController@store | org-scoped | yes |  |
| GET | `/api/org-members/list/{userId}` | Org\\Membership\\OrgMemberController@getMemberList | org-scoped | n/a | method missing/empty |
| POST | `/api/org-members/search` | Org\\Membership\\OrgMemberController@search | org-scoped | REVIEW |  |
| GET | `/api/org-membership-renewal-cycles` | Org\\Membership\\OrgMembershipRenewalCycleController@index | org-scoped | yes |  |
| POST | `/api/org-membership-renewal-cycles` | Org\\Membership\\OrgMembershipRenewalCycleController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/org-membership-renewal-cycles/{id}` | Org\\Membership\\OrgMembershipRenewalCycleController@destroy | org-scoped | yes |  |
| GET | `/api/org-membership-renewal-cycles/{id}` | Org\\Membership\\OrgMembershipRenewalCycleController@show | org-scoped | yes |  |
| PUT | `/api/org-membership-renewal-cycles/{id}` | Org\\Membership\\OrgMembershipRenewalCycleController@update | org-scoped | yes | saves $request->all() |
| GET | `/api/org-membership-renewal-prices` | Org\\Membership\\OrgMembershipRenewalPriceController@index | org-scoped | yes |  |
| POST | `/api/org-membership-renewal-prices` | Org\\Membership\\OrgMembershipRenewalPriceController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/org-membership-renewal-prices/{id}` | Org\\Membership\\OrgMembershipRenewalPriceController@destroy | org-scoped | yes |  |
| GET | `/api/org-membership-renewal-prices/{id}` | Org\\Membership\\OrgMembershipRenewalPriceController@show | org-scoped | yes |  |
| PUT | `/api/org-membership-renewal-prices/{id}` | Org\\Membership\\OrgMembershipRenewalPriceController@update | org-scoped | yes | saves $request->all() |
| GET | `/api/org-membership-renewals` | Org\\Membership\\OrgMembershipRenewalController@index | org-scoped | yes |  |
| POST | `/api/org-membership-renewals` | Org\\Membership\\OrgMembershipRenewalController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/org-membership-renewals/{id}` | Org\\Membership\\OrgMembershipRenewalController@destroy | org-scoped | yes |  |
| GET | `/api/org-membership-renewals/{id}` | Org\\Membership\\OrgMembershipRenewalController@show | org-scoped | yes |  |
| PUT | `/api/org-membership-renewals/{id}` | Org\\Membership\\OrgMembershipRenewalController@update | org-scoped | yes | saves $request->all() |
| GET | `/api/org-membership-types` | Org\\Membership\\OrgMembershipTypeController@index | org-scoped | yes |  |
| POST | `/api/org-membership-types` | Org\\Membership\\OrgMembershipTypeController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/org-membership-types/{id}` | Org\\Membership\\OrgMembershipTypeController@destroy | org-scoped | yes |  |
| GET | `/api/org-membership-types/{id}` | Org\\Membership\\OrgMembershipTypeController@show | org-scoped | yes |  |
| PUT | `/api/org-membership-types/{id}` | Org\\Membership\\OrgMembershipTypeController@update | org-scoped | yes | saves $request->all() |
| GET | `/api/org-next-meeting` | Org\\Meeting\\MeetingController@orgNextMeeting | org-scoped | yes |  |
| GET | `/api/org-profile-data/{userId}` | Org\\OrgProfileController@index | org-scoped | yes |  |
| PUT | `/api/org-profile-update/{userId}` | Org\\OrgProfileController@update | org-scoped | yes |  |
| GET | `/api/org-profile/logo` | Org\\OrgProfileController@getLogo | org-scoped | yes |  |
| POST | `/api/org-profile/logo/{userId}` | Org\\OrgProfileController@updateLogo | org-scoped | yes |  |
| GET | `/api/org-role-titles` | Role\\OrgRoleTitleController@index | org-scoped | yes |  |
| POST | `/api/org-role-titles` | Role\\OrgRoleTitleController@store | org-scoped | yes |  |
| DELETE | `/api/org-role-titles/{id}` | Role\\OrgRoleTitleController@destroy | org-scoped | yes |  |
| GET | `/api/org-role-titles/{id}` | Role\\OrgRoleTitleController@show | org-scoped | yes |  |
| PUT | `/api/org-role-titles/{id}` | Role\\OrgRoleTitleController@update | org-scoped | yes |  |
| GET | `/api/org-terminated-members` | Org\\Membership\\MembershipTerminationController@getOrgTerminatedMembers | org-scoped | yes |  |
| GET | `/api/org/switch` | Auth\\AuthController@switchOrg | per-user | yes |  |
| GET | `/api/permissions` | Role\\PermissionController@index | shared lookup (read) | yes |  |
| POST | `/api/permissions` | Role\\PermissionController@store | superadmin | yes (middleware) |  |
| DELETE | `/api/permissions/{id}` | Role\\PermissionController@destroy | superadmin | yes (middleware) |  |
| PUT | `/api/permissions/{id}` | Role\\PermissionController@update | superadmin | yes (middleware) |  |
| GET | `/api/phone-numbers` | Common\\PhoneNumberController@index | per-user | yes |  |
| POST | `/api/phone-numbers` | Common\\PhoneNumberController@store | per-user | yes |  |
| GET | `/api/phone-numbers/{id}` | Common\\PhoneNumberController@show | per-user | yes |  |
| PUT | `/api/phone-numbers/{id}` | Common\\PhoneNumberController@update | per-user | yes |  |
| GET | `/api/privacy-setups` | SuperAdmin\\Settings\\PrivacySetupController@index | shared lookup (read) | yes |  |
| POST | `/api/privacy-setups` | SuperAdmin\\Settings\\PrivacySetupController@store | superadmin | yes (middleware) | saves $request->all() |
| DELETE | `/api/privacy-setups/{id}` | SuperAdmin\\Settings\\PrivacySetupController@destroy | superadmin | yes (middleware) |  |
| PUT | `/api/privacy-setups/{id}` | SuperAdmin\\Settings\\PrivacySetupController@update | superadmin | yes (middleware) | saves $request->all() |
| GET | `/api/privacy-setups/all` | SuperAdmin\\Settings\\PrivacySetupController@getAllPrivacySetupForSuperAdmin | superadmin | yes (middleware) |  |
| GET | `/api/products` | Ecommerce\\Product\\ProductController@index | superadmin | yes (middleware) |  |
| POST | `/api/products` | Ecommerce\\Product\\ProductController@store | superadmin | yes (middleware) |  |
| DELETE | `/api/products/{id}` | Ecommerce\\Product\\ProductController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/products/{id}` | Ecommerce\\Product\\ProductController@show | superadmin | yes (middleware) |  |
| PUT | `/api/products/{id}` | Ecommerce\\Product\\ProductController@update | superadmin | yes (middleware) |  |
| GET | `/api/profileimage/{userId}` | Individual\\IndividualController@getProfileImage | per-user | yes |  |
| POST | `/api/profileimage/{userId}` | Individual\\IndividualController@updateProfileImage | per-user | yes |  |
| GET | `/api/project-attendances` | Org\\Project\\ProjectAttendanceController@index | org-scoped | yes |  |
| POST | `/api/project-attendances` | Org\\Project\\ProjectAttendanceController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/project-attendances/{id}` | Org\\Project\\ProjectAttendanceController@destroy | org-scoped | yes |  |
| GET | `/api/project-attendances/{id}` | Org\\Project\\ProjectAttendanceController@show | org-scoped | n/a | empty stub |
| PUT | `/api/project-attendances/{id}` | Org\\Project\\ProjectAttendanceController@update | org-scoped | yes | saves $request->all() |
| GET | `/api/project-guest-attendances` | Org\\Project\\ProjectGuestAttendanceController@index | org-scoped | yes |  |
| POST | `/api/project-guest-attendances` | Org\\Project\\ProjectGuestAttendanceController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/project-guest-attendances/{id}` | Org\\Project\\ProjectGuestAttendanceController@destroy | org-scoped | yes |  |
| GET | `/api/project-guest-attendances/{id}` | Org\\Project\\ProjectGuestAttendanceController@show | org-scoped | n/a | empty stub |
| PUT | `/api/project-guest-attendances/{id}` | Org\\Project\\ProjectGuestAttendanceController@update | org-scoped | yes | saves $request->all() |
| GET | `/api/project-summaries` | Org\\Project\\ProjectSummaryController@index | org-scoped | yes |  |
| POST | `/api/project-summaries` | Org\\Project\\ProjectSummaryController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/project-summaries/{id}` | Org\\Project\\ProjectSummaryController@destroy | org-scoped | yes |  |
| GET | `/api/project-summaries/{id}` | Org\\Project\\ProjectSummaryController@show | org-scoped | yes |  |
| POST | `/api/project-summaries/{id}` | Org\\Project\\ProjectSummaryController@update | org-scoped | yes | saves $request->all() |
| GET | `/api/projects` | Org\\Project\\ProjectController@index | org-scoped | yes |  |
| POST | `/api/projects` | Org\\Project\\ProjectController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/projects/{id}` | Org\\Project\\ProjectController@destroy | org-scoped | yes |  |
| GET | `/api/projects/{projectId}` | Org\\Project\\ProjectController@show | org-scoped | yes |  |
| POST | `/api/projects/{userId}` | Org\\Project\\ProjectController@update | org-scoped | yes | saves $request->all() |
| GET | `/api/receipts/org-receipts` | SuperAdmin\\Financial\\ReceiptController@orgIndex | org-scoped | yes |  |
| GET | `/api/recognitions` | Org\\Recognition\\RecognitionController@index | org-scoped | yes |  |
| POST | `/api/recognitions` | Org\\Recognition\\RecognitionController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/recognitions/{id}` | Org\\Recognition\\RecognitionController@destroy | org-scoped | yes |  |
| GET | `/api/recognitions/{id}` | Org\\Recognition\\RecognitionController@show | org-scoped | yes |  |
| POST | `/api/recognitions/{id}` | Org\\Recognition\\RecognitionController@update | org-scoped | yes |  |
| GET | `/api/referrals` | Common\\ReferralController@index | per-user | yes |  |
| GET | `/api/referrals/stats` | Common\\ReferralController@stats | per-user | yes |  |
| GET | `/api/region-currencies` | SuperAdmin\\Settings\\RegionCurrencyController@index | superadmin | yes (middleware) |  |
| POST | `/api/region-currencies` | SuperAdmin\\Settings\\RegionCurrencyController@store | superadmin | yes (middleware) | saves $request->all() |
| DELETE | `/api/region-currencies/{id}` | SuperAdmin\\Settings\\RegionCurrencyController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/region-currencies/{id}` | SuperAdmin\\Settings\\RegionCurrencyController@show | superadmin | yes (middleware) |  |
| PUT | `/api/region-currencies/{id}` | SuperAdmin\\Settings\\RegionCurrencyController@update | superadmin | yes (middleware) | saves $request->all() |
| GET | `/api/regional-tax-rates` | SuperAdmin\\Financial\\RegionalTaxRateController@index | superadmin | yes (middleware) |  |
| POST | `/api/regional-tax-rates` | SuperAdmin\\Financial\\RegionalTaxRateController@store | superadmin | yes (middleware) | saves $request->all() |
| DELETE | `/api/regional-tax-rates/{id}` | SuperAdmin\\Financial\\RegionalTaxRateController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/regional-tax-rates/{id}` | SuperAdmin\\Financial\\RegionalTaxRateController@show | superadmin | yes (middleware) |  |
| PUT | `/api/regional-tax-rates/{id}` | SuperAdmin\\Financial\\RegionalTaxRateController@update | superadmin | yes (middleware) | saves $request->all() |
| GET | `/api/regions` | SuperAdmin\\Settings\\RegionController@index | superadmin | yes (middleware) |  |
| POST | `/api/regions` | SuperAdmin\\Settings\\RegionController@store | superadmin | yes (middleware) | saves $request->all() |
| DELETE | `/api/regions/{id}` | SuperAdmin\\Settings\\RegionController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/regions/{id}` | SuperAdmin\\Settings\\RegionController@show | superadmin | yes (middleware) |  |
| PUT | `/api/regions/{id}` | SuperAdmin\\Settings\\RegionController@update | superadmin | yes (middleware) | saves $request->all() |
| POST | `/api/register` | Auth\\AuthController@register | public | yes | throttled |
| GET | `/api/reports` | Org\\Report\\OrgReportController@getIncomeReport | org-scoped | yes |  |
| GET | `/api/reports/membership-growth` | Org\\Report\\OrgReportController@getMembershipGrowthReport | org-scoped | yes |  |
| POST | `/api/reset-password` | Auth\\ForgotPasswordController@resetPassword | public | yes | throttled |
| GET | `/api/roles` | Role\\RoleController@index | shared lookup (read) | yes |  |
| POST | `/api/roles` | Role\\RoleController@store | superadmin | yes (middleware) |  |
| GET | `/api/roles-permissions` | Role\\RoleController@permissions | shared lookup (read) | yes |  |
| DELETE | `/api/roles/{id}` | Role\\RoleController@destroy | superadmin | yes (middleware) |  |
| PUT | `/api/roles/{id}` | Role\\RoleController@update | superadmin | yes (middleware) |  |
| PUT | `/api/roles/{role}/permissions` | Role\\UserRoleController@updateRolePermissions | superadmin | yes (middleware) |  |
| GET | `/api/strategic-plans` | Org\\StrategicPlan\\StrategicPlanController@index | org-scoped | yes |  |
| POST | `/api/strategic-plans` | Org\\StrategicPlan\\StrategicPlanController@store | org-scoped | yes | saves $request->all() |
| DELETE | `/api/strategic-plans/{id}` | Org\\StrategicPlan\\StrategicPlanController@destroy | org-scoped | yes |  |
| GET | `/api/strategic-plans/{id}` | Org\\StrategicPlan\\StrategicPlanController@show | org-scoped | yes |  |
| POST | `/api/strategic-plans/{id}` | Org\\StrategicPlan\\StrategicPlanController@update | org-scoped | yes | saves $request->all() |
| GET | `/api/sub-categories` | Ecommerce\\Category\\SubCategoryController@index | superadmin | yes (middleware) |  |
| POST | `/api/sub-categories` | Ecommerce\\Category\\SubCategoryController@store | superadmin | yes (middleware) |  |
| DELETE | `/api/sub-categories/{id}` | Ecommerce\\Category\\SubCategoryController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/sub-categories/{id}` | Ecommerce\\Category\\SubCategoryController@show | superadmin | yes (middleware) |  |
| PUT | `/api/sub-categories/{id}` | Ecommerce\\Category\\SubCategoryController@update | superadmin | yes (middleware) |  |
| GET | `/api/sub-sub-categories` | Ecommerce\\Category\\SubSubCategoryController@index | superadmin | yes (middleware) |  |
| POST | `/api/sub-sub-categories` | Ecommerce\\Category\\SubSubCategoryController@store | superadmin | yes (middleware) |  |
| DELETE | `/api/sub-sub-categories/{id}` | Ecommerce\\Category\\SubSubCategoryController@destroy | superadmin | yes (middleware) |  |
| GET | `/api/sub-sub-categories/{id}` | Ecommerce\\Category\\SubSubCategoryController@show | superadmin | yes (middleware) |  |
| PUT | `/api/sub-sub-categories/{id}` | Ecommerce\\Category\\SubSubCategoryController@update | superadmin | yes (middleware) |  |
| GET | `/api/success-stories` | Org\\SuccessStory\\SuccessStoryController@index | org-scoped | yes |  |
| POST | `/api/success-stories` | Org\\SuccessStory\\SuccessStoryController@store | org-scoped | yes |  |
| DELETE | `/api/success-stories/{id}` | Org\\SuccessStory\\SuccessStoryController@destroy | org-scoped | yes |  |
| GET | `/api/success-stories/{id}` | Org\\SuccessStory\\SuccessStoryController@show | org-scoped | yes |  |
| POST | `/api/success-stories/{id}` | Org\\SuccessStory\\SuccessStoryController@update | org-scoped | yes |  |
| GET | `/api/super_admin_profile_image/{userId}` | SuperAdmin\\SuperAdminController@getSuperAdminProfileImage | superadmin | yes (middleware) |  |
| POST | `/api/super_admin_profile_image/{userId}` | SuperAdmin\\SuperAdminController@updateSuperAdminProfileImage | superadmin | yes (middleware) |  |
| GET | `/api/super_admin_user_data/{id}` | SuperAdmin\\SuperAdminController@show | superadmin | yes (middleware) |  |
| GET | `/api/this-year-new-member-count` | Org\\Membership\\OrgMemberController@thisYearNewMemberCount | org-scoped | yes |  |
| GET | `/api/time-zone-setups` | SuperAdmin\\Settings\\TimeZoneSetupController@index | superadmin | yes (middleware) |  |
| POST | `/api/time-zone-setups` | SuperAdmin\\Settings\\TimeZoneSetupController@store | superadmin | yes (middleware) | saves $request->all() |
| DELETE | `/api/time-zone-setups/{id}` | SuperAdmin\\Settings\\TimeZoneSetupController@destroy | superadmin | yes (middleware) |  |
| PUT | `/api/time-zone-setups/{id}` | SuperAdmin\\Settings\\TimeZoneSetupController@update | superadmin | yes (middleware) | saves $request->all() |
| GET | `/api/total-org-member-count` | Org\\Membership\\OrgMemberController@totalOrgMemberCount | org-scoped | yes |  |
| GET | `/api/unlink-members` | Org\\Membership\\UnlinkMemberController@index | org-scoped | yes |  |
| POST | `/api/unlink-members` | Org\\Membership\\UnlinkMemberController@store | org-scoped | yes |  |
| DELETE | `/api/unlink-members/{id}` | Org\\Membership\\UnlinkMemberController@destroy | org-scoped | yes |  |
| GET | `/api/unlink-members/{id}` | Org\\Membership\\UnlinkMemberController@show | org-scoped | yes |  |
| PUT | `/api/unlink-members/{id}` | Org\\Membership\\UnlinkMemberController@update | org-scoped | yes |  |
| PUT | `/api/update-email/{userId}` | Auth\\AuthController@userEmailUpdate | per-user | yes |  |
| PUT | `/api/update-first-last-name/{userId}` | Auth\\AuthController@firstLastNameUpdate | per-user | yes |  |
| PUT | `/api/update-last-name/{userId}` | Auth\\AuthController@lastNameUpdate | per-user | yes |  |
| PUT | `/api/update-name/{userId}` | Auth\\AuthController@nameUpdate | per-user | yes |  |
| POST | `/api/update-password/{userId}` | Auth\\AuthController@updatePassword | per-user | yes |  |
| PUT | `/api/update-username/{userId}` | Auth\\AuthController@usernameUpdate | per-user | yes |  |
| GET | `/api/user-countries` | Common\\UserCountryController@index | shared lookup (read) | yes |  |
| POST | `/api/user-countries` | Common\\UserCountryController@store | per-user | yes | saves $request->all() |
| DELETE | `/api/user-countries/{id}` | Common\\UserCountryController@destroy | per-user | yes |  |
| GET | `/api/user-countries/{id}` | Common\\UserCountryController@show | per-user | n/a | empty stub |
| PUT | `/api/user-countries/{id}` | Common\\UserCountryController@update | per-user | yes | saves $request->all() |
| GET | `/api/user-countries/country-name` | Org\\OrgProfileController@getOrgCountry | org-scoped | yes |  |
| GET | `/api/user-languages` | Common\\UserLanguageController@index | per-user | n/a | empty stub |
| POST | `/api/user-languages` | Common\\UserLanguageController@store | per-user | yes | saves $request->all() |
| DELETE | `/api/user-languages/{id}` | Common\\UserLanguageController@destroy | per-user | n/a | empty stub |
| GET | `/api/user-languages/{id}` | Common\\UserLanguageController@show | per-user | n/a | empty stub |
| PUT | `/api/user-languages/{id}` | Common\\UserLanguageController@update | per-user | yes | saves $request->all() |
| GET | `/api/user-languages/language-name` | Common\\UserLanguageController@getUserLanguage | per-user | yes |  |
| GET | `/api/user-notifications` | Common\\UserNotificationController@index | per-user | yes |  |
| POST | `/api/user-notifications` | Common\\UserNotificationController@store | per-user | yes | saves $request->all() |
| DELETE | `/api/user-notifications/{id}` | Common\\UserNotificationController@destroy | per-user | yes |  |
| GET | `/api/user-notifications/{id}` | Common\\UserNotificationController@show | per-user | n/a | empty stub |
| PUT | `/api/user-notifications/{id}` | Common\\UserNotificationController@update | per-user | yes | saves $request->all() |
| GET | `/api/users` | Role\\UserRoleController@getUsers | org-scoped | yes |  |
| PUT | `/api/users/{user}/roles` | Role\\UserRoleController@assignRoles | org-scoped | yes |  |
| GET | `/api/verify-account/{uuid}` | Auth\\AuthController@verify | public | REVIEW |  |
| POST | `/api/verify-code` | Auth\\ForgotPasswordController@verifyResetCode | public | yes | throttled |
| GET | `/api/year-plans` | Org\\YearPlan\\YearPlanController@index | org-scoped | yes |  |
| POST | `/api/year-plans` | Org\\YearPlan\\YearPlanController@store | org-scoped | yes |  |
| DELETE | `/api/year-plans/{id}` | Org\\YearPlan\\YearPlanController@destroy | org-scoped | yes |  |
| GET | `/api/year-plans/{id}` | Org\\YearPlan\\YearPlanController@show | org-scoped | yes |  |
| POST | `/api/year-plans/{id}` | Org\\YearPlan\\YearPlanController@update | org-scoped | yes |  |

## Manual review of the remaining REVIEW rows

| Route | Verdict |
|---|---|
| `GET /api/management-packages`, `GET /api/management-subscriptions/currencies`, `/daily-price-rate`, `/management-package-prices` | Safe: platform price lists, read-only, same for every organisation |
| `POST /api/org-members/search` | Intended person directory search (add member / founder / administrator). Limited to 3+ characters, 20 results and display fields only (no email/phone) |
| `GET /api/verify-account/{uuid}` | Safe: public account verification by a random one-time UUID |

## Before / after (live requests as a local organisation account, org 10)

Read-only requests, run against `7bc8ab0` (before) and this branch (after). Write checks were run only on this branch, with requests that are refused before anything is saved.

| Request | Before | After |
|---|---|---|
| `GET /api/roles`, `/api/permissions` without login | 200 | 401 |
| `POST /api/roles` / `PUT /api/roles/{id}/permissions` as an organisation | allowed (no auth at all) | 403 |
| `POST /api/reset-password` with email only (no code) | password changed | 422 |
| `PUT /api/users/{id}/roles` for another organisation | allowed | 422 / own org only |
| `GET /api/users?type=organisation` | 30 organisations | 1 (own) |
| `GET /api/org-members-users/11` (other org) | 200 | 403 |
| `GET` another org's meeting / event / project / committee / asset / document / membership type | 200 | 404 |
| `GET /api/committee-members/1` (other org's committee) | 200 (3 members) | 404 |
| `POST` minutes / attendance / guests / summary / committee member on another org's record | allowed | 403 |
| `GET /api/office-documents` | 7 (all orgs) | 6 (own) |
| `GET /api/receipts/org-receipts` | 1 (another org's) | 0 |
| `GET /api/products`, `/api/super_admin_user_data/1` as an organisation | 200 | 403 |
| `POST /api/currencies`, `PUT /api/management-packages/1`, `DELETE /api/countries/{id}` as an organisation | allowed | 403 |
| `PUT /api/user-countries/{other person's id}` | allowed | 404 |
| `POST /api/org-members/search` with an empty query | every individual with email and phone | 422 |
| Failed request (e.g. meeting-minutes insert) | SQL statement in the response | generic message when APP_DEBUG=false |

Organisation screens (29 pages under `/org-dashboard`) loaded with no failed API calls after the change.

## Known remaining items

- About 85 create/update actions still save `$request->all()`. The owner and parent ids are now forced or checked, but other fillable fields (status flags, approval fields) are still taken from the client. Moving each to `$validator->validated()` is recommended.
- Individual and Super Admin screens were not exercised in a browser (no test accounts); their routes were checked statically.
- Unlinked-member terminations store the unlinked record id in `individual_type_user_id` (the column expects a user id). Needs a schema decision.
- Production must run with `APP_DEBUG=false`.
- Dated backup controllers (`*-14-12-2025.php`, `* copy.php`...) are not routed but still contain the old code; delete them.
