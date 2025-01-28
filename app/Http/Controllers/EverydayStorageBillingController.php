<?php

namespace App\Http\Controllers;

use App\Models\EverydayStorageBilling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
    public function store(Request $request)
    {
        $userId = $request->user()->id;
        $date = today();

        // Validate the request
        $request->validate([
            'user_id' => 'required|exists:users,id', // Ensure user_id exists in the users table
            'date' => 'required|date', // Ensure date is valid
        ]);

        // Calculate the price rate per member
        $storageDailyPriceRate = 0.03; // Your price rate per member

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

        return response()->json([
            'message' => 'Day total storage bill calculation successfully recorded.',
            'data' => [
                'user_id' => $userId,
                'date' => $date,
                'day_bill_amount' => $dayTotalBill,
            ],
        ]);
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
