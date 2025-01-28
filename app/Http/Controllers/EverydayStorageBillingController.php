<?php

namespace App\Http\Controllers;

use App\Models\EverydayStorageBilling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\StoragePricing;
use Carbon\Carbon;
 

class EverydayStorageBillingController extends Controller
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

    // public function userStoragePriceRate($userId)
    // {
    //     $regionalPrice = User::query()
    //         ->where('users.id', $userId)
    //         ->leftJoin('user_countries', 'users.id', '=', 'user_countries.user_id')
    //         ->leftJoin('country_regions', 'user_countries.country_id', '=', 'country_regions.country_id')
    //         ->leftJoin('subscriptions', 'users.id', '=', 'subscriptions.user_id')
    //         ->leftJoin('packages', 'subscriptions.package_id', '=', 'packages.id')
    //         ->leftJoin('region_currencies', 'country_regions.region_id', '=', 'region_currencies.region_id')
    //         ->leftJoin('currencies', 'region_currencies.currency_id', '=', 'currencies.id')
    //         ->leftJoin('regional_pricings', function ($join) {
    //             $join->on('regional_pricings.region_id', '=', 'country_regions.region_id')
    //                 ->on('regional_pricings.package_id', '=', 'subscriptions.package_id');
    //         })
    //         ->select('regional_pricings.price as regional_price_rate', 'packages.name as package_name', 'currencies.currency_code as user_currency_code')
    //         ->first();

    //     $price = $regionalPrice ? $regionalPrice->regional_price_rate : 0;
    //     $packageName = $regionalPrice? $regionalPrice->package_name : '';
    //     $userCurrencyCode = $regionalPrice? $regionalPrice->user_currency_code : '';

    //     return response()->json([
    //         'status' => true,
    //         'regional_price_rate' => $price,
    //         'package_name' => $packageName,
    //         'user_currency_code' => $userCurrencyCode
    //     ]);
    // }

    /**
     * Store a newly created resource in storage.
     */

     public function getUserStorageDailyPriceRate($userId)
     {
         // Get the user
         $user = User::with(['country.region', 'storageSubscription.package'])->findOrFail($userId);
     
         // Check if the user has a valid subscription
         $subscription = $user->subscription;
         if (!$subscription) {
             throw new \Exception("User does not have an active storage subscription.");
         }
     
         // Get the region ID from the user's country
         $region = $user->country->region;
         if (!$region) {
             throw new \Exception("Region not found for the user's country.");
         }
     
         // Find the price rate for the user's subscription package in their region
         $storagePriceRate = StoragePricing::where('region_id', $region->region_id)
             ->where('storage_package_id', $subscription->storage_package_id)
             ->value('price_rate');
     
        //  if (is_null($storagePriceRate)) {
        //      throw new \Exception("Price rate not found for the user's package in their region.");
        //  }

         if ($storagePriceRate === null) {
            throw new \Exception("Price rate not found for the user's package in their region.");
        }
     
         return $storagePriceRate;

        //  return response()->json([
        //     'daily_price_rate' => $storagePriceRate,
        // ]);
     }

    public function store(Request $request)
    {
        try {
            $users = User::where('type', 'organisation')->get();

            $singleUserData = $users->map(function ($user) {
                $userId = $user->id;
                $date = today();

                // Calculate the price rate per member
                //$storageDailyPriceRate = 0.03; // Your price rate per member
                $storageDailyPriceRate = $this->getUserStorageDailyPriceRate($userId);; // Your price rate per member

                // Calculate the total bill amount based on the members and price rate
                $dayTotalBill = 1 * $storageDailyPriceRate; // 1 for one day

                // Insert the count into org_member_counts
                DB::table('everyday_storage_billings')->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'date' => $date,
                    ],
                    [
                        'day_bill_amount' => $dayTotalBill,
                        'is_active' => true,
                    ]
                );
            });

            Log::info('Day storage bill calculation successfully recorded.');

            return response()->json([
                'message' => 'Day storage bill calculation successfully recorded.',
                'status' => true,
                'data' => $singleUserData,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EverydayStorageBilling $everydayStorageBilling)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EverydayStorageBilling $everydayStorageBilling)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EverydayStorageBilling $everydayStorageBilling)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EverydayStorageBilling $everydayStorageBilling)
    {
        //
    }
}
