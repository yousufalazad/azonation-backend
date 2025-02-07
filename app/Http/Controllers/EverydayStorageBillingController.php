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
        Log::info('User rate for: '. $userId);

         $user = User::with(['userCountry.country.countryRegion.region', 'storageSubscription.storagePackage'])->findOrFail($userId);

         Log::info('User getUserStorageDailyPriceRate: '. $user->id);

         $storageSubscriptionPackageData = $user->storageSubscription->storagePackage;
         $regionData = $user->userCountry->country->countryRegion->region;
         
         $storagePriceRate = StoragePricing::where('region_id', $regionData->id)
             ->where('storage_package_id', $storageSubscriptionPackageData->id)
             ->value('price_rate');

         return $storagePriceRate;
     }

    public function store(Request $request)
    {
        try {
            $users = User::where('type', 'organisation')->get();
            Log::info('User store ' . $users);
            $userData = $users->map(function ($user) {
                $userId = $user->id;
                $date = today();
                Log::info('User store ' . $userId);

                //Get storage daily price rate from other function
                $storageDailyPriceRate = $this->getUserStorageDailyPriceRate($userId);

                $dayTotalBill = 1 * $storageDailyPriceRate; // 1 for one day
                Log::info('User store ' . $dayTotalBill . ' fro user: ' . $userId);                

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