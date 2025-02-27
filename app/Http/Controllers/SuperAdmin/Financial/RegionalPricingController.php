<?php
namespace App\Http\Controllers\SuperAdmin\Financial;
use App\Http\Controllers\Controller;

use App\Models\RegionalPricing;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RegionalPricingController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(RegionalPricing $regionalPricing) {}
    public function edit(RegionalPricing $regionalPricing) {}
    public function update(Request $request, RegionalPricing $regionalPricing) {}
    public function getAllUserPriceRate()
    {
        try {
            $packageRegionCurrency = User::query()
                ->where('users.type', 'organisation')
                ->leftJoin('user_countries', 'users.id', '=', 'user_countries.user_id')
                ->leftJoin('country_regions', 'user_countries.country_id', '=', 'country_regions.country_id')
                ->leftJoin('region_currencies', 'country_regions.region_id', '=', 'region_currencies.region_id')
                ->leftJoin('currencies', 'region_currencies.currency_id', '=', 'currencies.id')
                ->leftJoin('subscriptions', 'users.id', '=', 'subscriptions.user_id')
                ->leftJoin('packages', 'subscriptions.package_id', '=', 'packages.id')
                ->leftJoin('regional_pricings', function ($join) {
                    $join->on('subscriptions.package_id', '=', 'regional_pricings.package_id')
                        ->on('country_regions.region_id', '=', 'regional_pricings.region_id');
                })
                ->select(
                    'users.id as user_id',
                    'users.name as user_name',
                    'subscriptions.package_id',
                    'packages.name as package_name',
                    'subscriptions.start_date as subscription_start_date',
                    'country_regions.region_id',
                    'currencies.currency_code',
                    'regional_pricings.price'
                )
                ->get();
            if ($packageRegionCurrency->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No price rates found for organisation users.'
                ], 404);
            }
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
            Log::error('Error fetching organisation users price rates: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
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
            $packageRegionCurrency = Subscription::query()
                ->leftJoin('country_regions', 'subscriptions.user_id', '=', 'country_regions.user_id')
                ->leftJoin('region_currencies', 'country_regions.region_id', '=', 'region_currencies.region_id')
                ->leftJoin('currencies', 'region_currencies.currency_id', '=', 'currencies.id')
                ->leftJoin('packages', 'subscriptions.package_id', '=', 'packages.id')
                ->leftJoin('users', 'country_regions.user_id', '=', 'users.id')
                ->leftJoin('regional_pricings', function ($join) {
                    $join->on('subscriptions.package_id', '=', 'regional_pricings.package_id')
                        ->on('country_regions.region_id', '=', 'regional_pricings.region_id');
                })
                ->select(
                    'users.name as user_name',
                    'subscriptions.user_id',
                    'subscriptions.package_id',
                    'packages.name as package_name',
                    'subscriptions.start_date as subscription_start_date',
                    'country_regions.region_id',
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
            Log::error('Error fetching all users price rates: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
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
            $user_id = $request->user()->id;
            $packageRegionCurrency = Subscription::where('subscriptions.user_id', $user_id)
                ->leftJoin('country_regions', 'subscriptions.user_id', '=', 'country_regions.user_id')
                ->leftJoin('region_currencies', 'country_regions.region_id', '=', 'region_currencies.region_id')
                ->leftJoin('currencies', 'region_currencies.currency_id', '=', 'currencies.id')
                ->select('subscriptions.package_id', 'country_regions.region_id', 'currencies.currency_code')
                ->first();
            if (!$packageRegionCurrency) {
                return response()->json([
                    'status' => false,
                    'message' => 'No price rate found for the user.'
                ], 404);
            }
            $packageId = $packageRegionCurrency->package_id;
            $regionId = $packageRegionCurrency->region_id;
            $currencyCode = $packageRegionCurrency->currency_code;
            if (!$regionId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unable to determine regionId for the given country.'
                ], 400);
            }
            $userPriceRate = RegionalPricing::where('package_id', $packageId)
                ->where('region_id', $regionId)
                ->select('regional_pricings.price')
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
            Log::error('Error fetching user price rates: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
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
    public function destroy(RegionalPricing $regionalPricing) {}
}
