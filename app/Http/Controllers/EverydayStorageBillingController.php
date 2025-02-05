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
        Log::info('rate started for user '. $userId); 
        
         $user = User::with(['userCountry.country.countryRegion.region', 'storageSubscription.package'])->findOrFail($userId);
     
         Log::info('User data: '. $user);

         $storageSubscriptionPackageData = $user->storageSubscription->package;
        //  if (!$storageSubscriptionPackageData) {
        //      throw new \Exception("User does not have an active storage subscription.");
        //  }
         
         Log::info(' subscription 2: '. $storageSubscriptionPackageData->id);

         $regionData = $user->userCountry->country->countryRegion->region;
            Log::info("price 3--- Region data: ". $regionData->id);

        //  if (!$regionData) {
        //      throw new \Exception("Region not found for the user's country.");
        //  }
         
         $storagePriceRate = StoragePricing::where('region_id', $regionData->id)
             ->where('storage_package_id', $storageSubscriptionPackageData->id)
             ->value('price_rate');
     
             Log::info('Daily price rate for '. $userId. ' is '. $storagePriceRate);

        //  if (is_null($storagePriceRate)) {
        //      throw new \Exception("Price rate not found for the user's package in their region.");
        //  }

        //  if ($storagePriceRate === null) {
        //     throw new \Exception("Price rate not found for the user's package in their region.");
        // }
     
         return $storagePriceRate;

        //  return response()->json([
        //     'daily_price_rate' => $storagePriceRate,
        // ]);
     }

    public function store(Request $request)
    {
        Log::info('very bigenning');
        try {
            $users = User::where('type', 'organisation')->get();

            Log::info('Everyday storage bill generation '. $users);

            $userData = $users->map(function ($user) {
                Log::info('single data fetch started '. $user->id);

                $userId = $user->id;
                $date = today();

                $storageDailyPriceRate = $this->getUserStorageDailyPriceRate($userId);


                $dayTotalBill = 1 * $storageDailyPriceRate; // 1 for one day

                Log::info('Total bill amount: '. $dayTotalBill);

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
