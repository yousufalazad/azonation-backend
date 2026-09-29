<?php

namespace App\Http\Controllers\SuperAdmin\Financial\Management;

use App\Http\Controllers\Controller;

use App\Models\ManagementSubscription;
use App\Models\ManagementSubscriptionRecord;
use App\Models\ManagementPricing;
use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use Spatie\Permission\Models\Role;
use App\Models\OrgMemberRoleTitle;
use App\Models\OrgRoleTitle;


class ManagementSubscriptionController extends Controller
{
    public function index()
    {
        try {
            $userId = Auth::id();

            $managementSubscriptions = ManagementSubscription::where('user_id', $userId)
                ->where('is_active', 1)
                ->with(['managementPackage'])
                ->get();

            return response()->json([
                'status' => true,
                'data' => $managementSubscriptions,
                'message' => 'Subscriptions fetched successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching subscriptions',
                'error' => \App\Support\ErrorDetail::for($e)
            ], 500);
        }
    }


    public function managementPriceRate()
    {
        try {
            $userId = Auth::id();

            $user = User::with([
                'userCountry.country.countryRegion.region',
                'managementSubscription.managementPackage'
            ])->findOrFail($userId);

            $region = $user->userCountry->country->countryRegion->region;

            $managementPackage = $user->managementSubscription->managementPackage;

            $managementPriceRate = ManagementPricing::where('region_id', $region->id)
                ->where('management_package_id', $managementPackage->id)
                ->value('price_rate');

            if ($managementPriceRate) {
                return response()->json([
                    'daily_price_rate' => $managementPriceRate,
                    'status' => true,
                    'message' => 'Daily price rate fetched successfully'
                ], 200);
            } else {
                return response()->json([
                    'error' => 'Price rate not found for the user\'s region and package',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching the daily price rate',
                'message' => \App\Support\ErrorDetail::for($e),
            ], 500);
        }
    }


    public function managementPackagePrices()
    {
        try {
            $userId = Auth::id();

            $user = User::with([
                'userCountry.country.countryRegion.region',
                'managementSubscription.managementPackage'
            ])->findOrFail($userId);

            $region = $user->userCountry->country->countryRegion->region;

            $managementPackagePrices = ManagementPricing::where(
                'region_id',
                $region->id
            )->get();

            if ($managementPackagePrices->isNotEmpty()) {
                return response()->json([
                    'package_prices' => $managementPackagePrices,
                    'status' => true,
                    'message' => 'Package prices fetched successfully'
                ], 200);
            } else {
                return response()->json([
                    'error' => 'Price rate not found for the user\'s region and package',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching the daily price rate',
                'message' => \App\Support\ErrorDetail::for($e),
            ], 500);
        }
    }


    public function currency()
    {
        try {
            $userId = Auth::id();

            $user = User::with([
                'userCountry.country.countryRegion.regionCurrency.currency'
            ])->findOrFail($userId);

            $currency = $user->userCountry->country?->countryRegion?->region?->regionCurrency?->currency;

            if ($currency) {
                return response()->json([
                    'data' => $currency,
                    'status' => true,
                    'message' => 'Currency fetched successfully'
                ], 200);
            } else {
                return response()->json([
                    'error' => 'Currency not found for the user\'s region',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching the currency',
                'message' => \App\Support\ErrorDetail::for($e),
            ], 500);
        }
    }


    public function store(Request $request)
    {
        //
    }


    public function show(ManagementSubscription $managementSubscription)
    {
        //
    }


    public function edit(ManagementSubscription $managementSubscription)
    {
        //
    }


    public function update(Request $request, $id)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | Validate Request
            |--------------------------------------------------------------------------
            */
            $validated = $request->validate([
                'user_id' => 'required|integer',
                'management_package_id' => 'required|integer',
                'start_date' => 'nullable|date',
                'is_active' => 'nullable|boolean',
            ]);

            // Organisations can only change their own subscription; Super Admins any
            $ownerOnly = $request->user()?->type === 'superadmin' ? null : $request->user()->id;
            if ($ownerOnly) {
                $validated['user_id'] = $ownerOnly;
            }


            /*
            |--------------------------------------------------------------------------
            | Database Transaction
            |--------------------------------------------------------------------------
            */
            $result = DB::transaction(function () use ($validated, $id, $ownerOnly) {

                /*
                |--------------------------------------------------------------------------
                | 1. Get Current Subscription
                |--------------------------------------------------------------------------
                */
                $subscription = ManagementSubscription::with([
                    'managementPackage'
                ])->when($ownerOnly, fn ($q) => $q->where('user_id', $ownerOnly))->find($id);


                /*
                |--------------------------------------------------------------------------
                | Subscription Not Found
                |--------------------------------------------------------------------------
                */
                if (!$subscription) {
                    return [
                        'success' => false,
                        'response' => response()->json([
                            'status' => false,
                            'message' => 'Management subscription not found.',
                        ], 404)
                    ];
                }


                /*
                |--------------------------------------------------------------------------
                | 2. Store OLD Subscription Information
                |--------------------------------------------------------------------------
                */
                $oldPackageId = $subscription->management_package_id;

                $oldPackageName = $subscription->managementPackage?->name;

                $oldStartDate = $subscription->start_date;

                $oldEndDate = $validated['start_date'];


                /*
                |--------------------------------------------------------------------------
                | 3. Get OLD Package Price
                |--------------------------------------------------------------------------
                | Get user's current region and old package price.
                */
                $oldPriceRate = null;

                $user = User::with([
                    'userCountry.country.countryRegion.region',
                ])->find($subscription->user_id);

                if ($user) {

                    $region = $user->userCountry?->country?->countryRegion?->region;

                    if ($region && $oldPackageId) {

                        $oldPriceRate = ManagementPricing::where(
                            'region_id',
                            $region->id
                        )
                            ->where(
                                'management_package_id',
                                $oldPackageId
                            )
                            ->value('price_rate');
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | 4. Get NEW Package Information
                |--------------------------------------------------------------------------
                */
                $newPackageId = $validated['management_package_id'];

                $newPackage = \App\Models\ManagementPackage::find(
                    $newPackageId
                );

                $newPackageName = $newPackage?->name;


                /*
                |--------------------------------------------------------------------------
                | 5. Get NEW Package Price
                |--------------------------------------------------------------------------
                */
                $newPriceRate = null;

                if ($user) {

                    $region = $user->userCountry?->country?->countryRegion?->region;

                    if ($region && $newPackageId) {

                        $newPriceRate = ManagementPricing::where(
                            'region_id',
                            $region->id
                        )
                            ->where(
                                'management_package_id',
                                $newPackageId
                            )
                            ->value('price_rate');
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | 6. Get Currency Code
                |--------------------------------------------------------------------------
                */
                $currencyCode = null;

                if ($user) {

                    $currency = User::with([
                        'userCountry.country.countryRegion.regionCurrency.currency'
                    ])->find($subscription->user_id);

                    $currencyCode = $currency
                        ?->userCountry
                        ?->country
                        ?->countryRegion
                        ?->region
                        ?->regionCurrency
                        ?->currency
                        ?->code;
                }
                $currencyCode = 'USD';


                /*
                |--------------------------------------------------------------------------
                | 7. Insert OLD + NEW Data into Record Table
                |-------------------------------------------------------------------------
                */
                $subscriptionRecord = ManagementSubscriptionRecord::create([
                    /*
                    |--------------------------------------------------------------------------
                    | User
                    |--------------------------------------------------------------------------
                    */
                    'user_id' => $validated['user_id'],


                    /*
                    |--------------------------------------------------------------------------
                    | OLD PACKAGE
                    |--------------------------------------------------------------------------
                    */
                    'old_mgmt_pakg_id' => $oldPackageId,

                    'old_mgmt_pakg_name' => $oldPackageName,

                    'old_mgmt_price_rate' => $oldPriceRate,

                    'old_mgmt_pakg_start_date' => $oldStartDate,

                    'old_mgmt_pakg_end_date' => $oldEndDate,


                    /*
                    |--------------------------------------------------------------------------
                    | NEW PACKAGE
                    |--------------------------------------------------------------------------
                    */
                    'new_mgmt_pakg_id' => $newPackageId,

                    'new_mgmt_pakg_name' => $newPackageName,

                    'new_mgmt_price_rate' => $newPriceRate,


                    /*
                    |--------------------------------------------------------------------------
                    | CURRENCY
                    |--------------------------------------------------------------------------
                    */
                    'currency_code' => $currencyCode,


                    /*
                    |--------------------------------------------------------------------------
                    | CHANGE INFORMATION
                    |--------------------------------------------------------------------------
                    */
                    'change_date' => now(),

                    'change_reason' => 'Package changed',

                    'is_active' => $validated['is_active'] ?? 1,
                ]);


                /*
                |--------------------------------------------------------------------------
                | 8. Update Current Management Subscription
                |--------------------------------------------------------------------------
                */
                $subscription->user_id =  $validated['user_id'];
                $subscription->management_package_id = $validated['management_package_id'];
                if (!empty($validated['start_date'])) {
                    $subscription->start_date = $validated['start_date'];
                }
                $subscription->is_active = $validated['is_active'] ?? 1;
                $subscription->subscription_status = 'active';
                $subscription->save();

                $user_id = $validated['user_id'];
                $updatedOrgAccess = null;

                if ($user_id) {
                    // get management_subscriptions for $user_id and subscription_status = active and is_active = 1 join with management_packages and get the package name and price rate
                    $managementSubscription = ManagementSubscription::where('management_subscriptions.user_id', $user_id)
                        ->where('management_subscriptions.subscription_status', 'active')
                        ->where('management_subscriptions.is_active', 1)
                        ->join('management_packages', 'management_subscriptions.management_package_id', '=', 'management_packages.id')
                        ->select('management_subscriptions.*', 'management_packages.name as subscription_package_name', 'management_packages.slug as subscription_package_slug')
                        ->first();

                    if ($managementSubscription) {
                        $this->assignUserRoles($user_id, $managementSubscription->subscription_package_slug, $user_id, 'admin', false);
                        
                        $orgOwner = User::find($user_id);
                        if ($orgOwner) {
                            $authController = new \App\Http\Controllers\Auth\AuthController();
                            $fullOrgAccess = $authController->getOrgAccess($orgOwner);

                            // an organisation admin's own org_type_user_id is their own user id
                            // (see AuthController::register -> assignUserRoles($user->id, ..., $user->id, 'admin', true))
                            $updatedOrgAccess = collect($fullOrgAccess)
                                ->firstWhere('org_type_user_id', $user_id);
                        }
                    }
                }

                return [
                    'success' => true,
                    'response' => response()->json([
                        'status' => true,
                        'message' => 'Subscription updated successfully.',
                        'data' => $subscription,
                        'record' => $subscriptionRecord,
                        'org_access' => $updatedOrgAccess, // ADDED: fresh roles/permissions for this org
                    ], 200)
                ];
            });


            /*
            |--------------------------------------------------------------------------
            | Return Transaction Result
            |--------------------------------------------------------------------------
            */
            return $result['response'];
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'error' => \App\Support\ErrorDetail::for($e),
            ], 500);
        }
    }

    private function X_assignUserRoles($userId, array $roles, $orgTypeUserId, $orgRoleTitle = null, $isNewUser = false)
    {
        DB::beginTransaction();

        try {
            $user = User::findOrFail($userId);

            // IMPORTANT: roles are now org-based
            $roleModels = Role::whereIn('name', $roles)
                ->where('guard_name', 'web')
                ->get();

            // Existing user cleanup only
            if (!$isNewUser) {

                $existingRoleIds = DB::table('model_has_roles')
                    ->where('model_type', User::class)
                    ->where('model_id', $user->id)
                    ->pluck('role_id')
                    ->toArray();

                $newRoleIds = $roleModels->pluck('id')->toArray();

                $toDelete = array_diff($existingRoleIds, $newRoleIds);

                if (!empty($toDelete)) {
                    DB::table('model_has_roles')
                        ->whereIn('role_id', $toDelete)
                        ->where('model_type', User::class)
                        ->where('model_id', $user->id)
                        ->delete();
                }
            }

            // Insert roles
            foreach ($roleModels as $role) {
                DB::table('model_has_roles')->updateOrInsert(
                    [
                        'role_id' => $role->id,
                        'model_type' => User::class,
                        'model_id' => $user->id,
                    ],
                    [
                        'org_type_user_id' => $orgTypeUserId
                    ]
                );
            }
            // table org_role_titles insert/update
            if ($orgRoleTitle) {
                $orgRoleTitleData = OrgRoleTitle::updateOrCreate(
                    [
                        'org_type_user_id' => $orgTypeUserId,
                        'name' => $orgRoleTitle,
                    ],

                );
            }
            // Org role title (IMPORTANT: should be org-based unique)
            if ($orgRoleTitleData) {
                OrgMemberRoleTitle::updateOrCreate(
                    [
                        'org_type_user_id' => $orgTypeUserId,
                        'individual_type_user_id' => $userId,
                    ],
                    [
                        'org_role_title_id' => $orgRoleTitleData['id'],
                    ]
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    private function assignUserRoles($userId, $roles, $orgTypeUserId, $orgRoleTitle = null, $isNewUser = false)
    {
        DB::beginTransaction();

        try {
            $user = User::findOrFail($userId);

            $role = Role::where('name', $roles)
                ->where('guard_name', 'web')
                ->first();

            if (!$role) {
                throw new \Exception('Role not found.');
            }

            $existingRole = DB::table('model_has_roles')
                ->where('model_type', User::class)
                ->where('model_id', $user->id)
                ->where('org_type_user_id', $orgTypeUserId)
                ->first();

            if ($existingRole) {
                DB::table('model_has_roles')
                    ->where('model_type', User::class)
                    ->where('model_id', $user->id)
                    ->where('org_type_user_id', $orgTypeUserId)
                    ->update([
                        'role_id' => $role->id,
                    ]);
            } else {
                DB::table('model_has_roles')->insert([
                    'role_id' => $role->id,
                    'model_type' => User::class,
                    'model_id' => $user->id,
                    'org_type_user_id' => $orgTypeUserId,
                ]);
            }

            $orgRoleTitleData = null;

            if ($orgRoleTitle) {
                $orgRoleTitleData = OrgRoleTitle::updateOrCreate(
                    [
                        'org_type_user_id' => $orgTypeUserId,
                        'name' => $orgRoleTitle,
                    ]
                );
            }

            if ($orgRoleTitleData) {
                OrgMemberRoleTitle::updateOrCreate(
                    [
                        'org_type_user_id' => $orgTypeUserId,
                        'individual_type_user_id' => $userId,
                    ],
                    [
                        'org_role_title_id' => $orgRoleTitleData->id,
                    ]
                );
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function destroy(ManagementSubscription $managementSubscription)
    {
        //
    }
}
