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


     public function getUserStorageDailyPriceRate($userId)
     {
        Log::info('getUserStorageDailyPriceRate started for 7'. $userId); 
        
        // Get the user
         $user = User::with(['country.region', 'storageSubscription.package'])->findOrFail($userId);
     
         Log::info('getUserStorageDailyPriceRate user data found 8 ->'. $user->id);

         //Check if the user has a valid subscription
         $subscription = $user->storageSubscription;
        //  if (!$subscription) {
        //      throw new \Exception("User does not have an active storage subscription.");
        //  }
         
         Log::info('getUserStorageDailyPriceRate subscription found 9'. $subscription->id);

         // Get the region ID from the user's country
         $region = $user->country->region;
         if (!$region) {
             throw new \Exception("Region not found for the user's country.");
         }
         
         Log::info('getUserStorageDailyPriceRate region found 10'. $region->region_id);

         // Find the price rate for the user's subscription package in their region
         $storagePriceRate = StoragePricing::where('region_id', $region->region_id)
             ->where('storage_package_id', $subscription->storage_package_id)
             ->value('price_rate');
     
             Log::info('Daily price rate for '. $userId. ' is '. $storagePriceRate);

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
        Log::info('Everyday storage bill generation started. 1');
        try {
            $users = User::where('type', 'organisation')->get();
            Log::info('Everyday storage bill generation started for '. $users->count().'users. 2');

            $userData = $users->map(function ($user) {
                Log::info('Everyday storage bill generation started for 3 '. $user->id);
                $userId = $user->id;
                $date = today();

                // Calculate the price rate per member
                //$storageDailyPriceRate = 0.03; // Your price rate per member
                $storageDailyPriceRate = $this->getUserStorageDailyPriceRate($userId);; // Your price rate per member

                Log::info('Daily price rate for '. $userId. ' is 4'. $storageDailyPriceRate);

                // Calculate the total bill amount based on the members and price rate
                $dayTotalBill = 1 * $storageDailyPriceRate; // 1 for one day

                Log::info('hmm. 5');

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
                Log::info('hmm 6');
            });

            

            return response()->json([
                'message' => 'Day storage bill calculation successfully recorded.',
                'status' => true,
                'data' => $userData,
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
