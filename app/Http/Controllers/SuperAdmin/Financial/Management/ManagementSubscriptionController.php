<?php

namespace App\Http\Controllers\SuperAdmin\Financial\Management;

use App\Http\Controllers\Controller;

use App\Models\ManagementSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ManagementPricing;


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

    

    public function managementPriceRate() {
        try {
            $userId = Auth::id();
            $user = User::with(['userCountry.country.countryRegion.region', 'managementSubscription.managementPackage'])->findOrFail($userId);
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

    public function currency() {
        try {
            $userId = Auth::id();
            $user = User::with(['userCountry.country.countryRegion.regionCurrency.currency'])->findOrFail($userId);
    
            // $currency = optional($user->userCountry)->country?->countryRegion?->regionCurrency?->currency;
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
    


    public function currencyTest() {
        try {
            $userId = Auth::id();
            $user = User::with(['userCountry.country.countryRegion.regionCurrency.currency'])->findOrFail($userId);
            $currency = $user->userCountry->country->countryRegion->regionCurrency->currency;

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
    public function store(Request $request) {}
    public function show(ManagementSubscription $managementSubscription) {}
    public function edit(ManagementSubscription $managementSubscription) {}
    public function update(Request $request, ManagementSubscription $managementSubscription) {}
    public function destroy(ManagementSubscription $managementSubscription) {}
}
