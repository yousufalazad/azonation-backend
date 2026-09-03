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
                'error' => $e->getMessage()
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
                'message' => $e->getMessage(),
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
                'message' => $e->getMessage(),
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
                'message' => $e->getMessage(),
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


            /*
            |--------------------------------------------------------------------------
            | Database Transaction
            |--------------------------------------------------------------------------
            */
            $result = DB::transaction(function () use ($validated, $id) {

                /*
                |--------------------------------------------------------------------------
                | 1. Get Current Subscription
                |--------------------------------------------------------------------------
                */
                $subscription = ManagementSubscription::with([
                    'managementPackage'
                ])->find($id);


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
                |
                | Get user's current region and old package price.
                |
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
                $currencyCode = 'BDT';


                /*
                |--------------------------------------------------------------------------
                | 7. Insert OLD + NEW Data into Record Table
                |--------------------------------------------------------------------------
                |
                | IMPORTANT:
                | This happens BEFORE updating ManagementSubscription.
                |
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
                $subscription->user_id = $validated['user_id'];

                $subscription->management_package_id =
                    $validated['management_package_id'];


                /*
                |--------------------------------------------------------------------------
                | Update Start Date
                |--------------------------------------------------------------------------
                */
                if (!empty($validated['start_date'])) {

                    $subscription->start_date = $validated['start_date'];
                }


                /*
                |--------------------------------------------------------------------------
                | Active Status
                |--------------------------------------------------------------------------
                */
                $subscription->is_active =
                    $validated['is_active'] ?? 1;


                /*
                |--------------------------------------------------------------------------
                | Subscription Status
                |--------------------------------------------------------------------------
                */
                $subscription->subscription_status = 'active';


                /*
                |--------------------------------------------------------------------------
                | Save Subscription
                |--------------------------------------------------------------------------
                */
                $subscription->save();


                /*
                |--------------------------------------------------------------------------
                | Return Success
                |--------------------------------------------------------------------------
                */
                return [
                    'success' => true,
                    'response' => response()->json([
                        'status' => true,
                        'message' => 'Subscription updated successfully.',
                        'data' => $subscription,
                        'record' => $subscriptionRecord,
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
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy(ManagementSubscription $managementSubscription)
    {
        //
    }
}