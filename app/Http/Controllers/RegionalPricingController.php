<?php

namespace App\Http\Controllers;

use App\Models\RegionalPricing;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class RegionalPricingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(RegionalPricing $regionalPricing)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RegionalPricing $regionalPricing)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RegionalPricing $regionalPricing)
    {
        //
    }

    public function getAllUserPriceRate()
    {
        try {
            // Fetch all users of type 'organisation' and their price rates
            $packageRegionCurrency = User::query()
                ->where('users.type', 'organisation') // Filter users by type
                ->leftJoin('subscriptions', 'users.id', '=', 'subscriptions.user_id')
                ->leftJoin('user_regions', 'users.id', '=', 'user_regions.user_id')
                ->leftJoin('region_currencies', 'user_regions.region_id', '=', 'region_currencies.region_id')
                ->leftJoin('currencies', 'region_currencies.currency_id', '=', 'currencies.id')
                ->leftJoin('packages', 'subscriptions.package_id', '=', 'packages.id')
                ->leftJoin('regional_pricings', function ($join) {
                    $join->on('subscriptions.package_id', '=', 'regional_pricings.package_id')
                        ->on('user_regions.region_id', '=', 'regional_pricings.region_id');
                })
                ->select(
                    'users.id as user_id',
                    'users.name as user_name',
                    'subscriptions.package_id',
                    'packages.name as package_name',
                    'subscriptions.start_date as subscription_start_date',
                    'user_regions.region_id',
                    'currencies.currency_code',
                    'regional_pricings.price'
                )
                ->get();

            // Check if the result is empty
            if ($packageRegionCurrency->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No price rates found for organisation users.'
                ], 404);
            }

            // Transform data for a better response format
            $result = $packageRegionCurrency->map(function ($record) {
                return [
                    'user_id' => $record->user_id,
                    'user_name' => $record->user_name,
                    'package_id' => $record->package_id,
                    'package_name' => $record->package_name,
                    'subscription_start_date' => $record->subscription_start_date,
                    'region_id' => $record->region_id,
                    'currency_code' => $record->currency_code,
                    'price' => $record->price,
                ];
            });

            return response()->json([
                'status' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error fetching organisation users price rates: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return error details in the response
            return response()->json([
                'status' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            ], 500);
        }
    }


    public function getAllUserPriceRateWithoutType()
    {
        try {
            // Fetch all users' price rates
            $packageRegionCurrency = Subscription::query()
                ->leftJoin('user_regions', 'subscriptions.user_id', '=', 'user_regions.user_id')
                ->leftJoin('region_currencies', 'user_regions.region_id', '=', 'region_currencies.region_id')
                ->leftJoin('currencies', 'region_currencies.currency_id', '=', 'currencies.id')
                ->leftJoin('packages', 'subscriptions.package_id', '=', 'packages.id')
                ->leftJoin('users', 'user_regions.user_id', '=', 'users.id')
                ->leftJoin('regional_pricings', function ($join) {
                    $join->on('subscriptions.package_id', '=', 'regional_pricings.package_id')
                        ->on('user_regions.region_id', '=', 'regional_pricings.region_id');
                })
                ->select(
                    'users.name as user_name',
                    'subscriptions.user_id',
                    'subscriptions.package_id',
                    'packages.name as package_name',
                    'subscriptions.start_date as subscription_start_date',
                    'user_regions.region_id',
                    'currencies.currency_code',
                    'regional_pricings.price'
                )
                ->get();

            if ($packageRegionCurrency->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No price rates found for users.'
                ], 404);
            }

            // Transform data for a better response format
            $result = $packageRegionCurrency->map(function ($record) {
                return [
                    'id' => $record['id'],
                    'user_id' => $record->user_id,
                    'package_id' => $record->package_id,
                    'region_id' => $record->region_id,
                    'currency_code' => $record->currency_code,
                    'price' => $record->price,
                    'user_name' => $record->user_name,
                    'package_name' => $record->package_name,
                    'subscription_start_date' => $record->subscription_start_date,
                ];
            });

            return response()->json([
                'status' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error fetching all users price rates: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return error details in the response
            return response()->json([
                'status' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            ], 500);
        }
    }


    public function getUserPriceRate(Request $request)
    {
        try {
            // Retrieve the authenticated user's ID
            $user_id = $request->user()->id;

            // Fetch user price rate
            $packageRegionCurrency = Subscription::where('subscriptions.user_id', $user_id)
                ->leftJoin('user_regions', 'subscriptions.user_id', '=', 'user_regions.user_id')
                ->leftJoin('region_currencies', 'user_regions.region_id', '=', 'region_currencies.region_id')
                ->leftJoin('currencies', 'region_currencies.currency_id', '=', 'currencies.id')
                ->select('subscriptions.package_id', 'user_regions.region_id', 'currencies.currency_code')
                ->first(); // Retrieve a single record

            if (!$packageRegionCurrency) {
                return response()->json([
                    'status' => false,
                    'message' => 'No price rate found for the user.'
                ], 404);
            }

            // Extract package_id, region_id and currency_code
            $packageId = $packageRegionCurrency->package_id;
            $regionId = $packageRegionCurrency->region_id;
            $currencyCode = $packageRegionCurrency->currency_code;

            // Validate regionKey
            if (!$regionId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unable to determine regionId for the given country.'
                ], 400);
            }

            // Fetch the price rate for the specified package and region
            $userPriceRate = RegionalPricing::where('package_id', $packageId)
                ->where('region_id', $regionId)
                ->select('regional_pricings.price') // Fetch only the relevant one region column
                ->first();

            if (!$userPriceRate) {
                return response()->json([
                    'status' => false,
                    'message' => 'No price rate found for the provided package and region.'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'id' => 1,
                    'price' => $userPriceRate->price,
                    'currency_code' => $currencyCode
                ]
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error fetching user price rates: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return error details in the response
            return response()->json([
                'status' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RegionalPricing $regionalPricing)
    {
        //
    }
}
