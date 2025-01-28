<?php

namespace App\Http\Controllers;

use App\Models\ManagementBilling;
use App\Models\EverydayMemberCountAndBilling;
use App\Models\EverydayStorageBilling;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;




class ManagementBillingController extends Controller
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

    public function getUserCurrency($userId)
    {
        try {
            // Fetch user by ID
            $user = User::findOrFail($userId);

            // Traverse relationships to find the currency
            $userCurrency = $user->userCountry // Fetch user's country
                ->country // Access the associated country
                ->region // Access the associated region
                ->regionCurrency // Fetch region's currency
                ->currency; // Get the actual currency

            // If currency exists, return the currency details
            if ($userCurrency) {
                return response()->json([
                    'currency_code' => $userCurrency->currency_code,
                ]);
            }

            // If no currency is found, return a 404 response
            return response()->json([
                'error' => 'Currency not found for the user',
            ], 404);
        } catch (\Exception $e) {
            // Handle errors gracefully
            return response()->json([
                'error' => 'An error occurred while retrieving the user currency.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }


    public function billCalculation($userId)
    {
        // Get the start and end dates of the previous month
        $startOfPreviousMonth = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $endOfPreviousMonth = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        $monthlyTotalMemberCount = EverydayMemberCountAndBilling::where('user_id', $userId)
            ->whereBetween('date', [$startOfPreviousMonth, $endOfPreviousMonth])
            ->sum('day_total_member');

        $monthlyTotalManagementBillAmount = EverydayMemberCountAndBilling::where('user_id', $userId)
            ->whereBetween('date', [$startOfPreviousMonth, $endOfPreviousMonth])
            ->sum('day_total_bill');

        $monthlyTotalStorageBillAmount = EverydayStorageBilling::where('user_id', $userId)
            ->whereBetween('date', [$startOfPreviousMonth, $endOfPreviousMonth])
            ->sum('day_bill_amount');

        return response()->json([
            'status' => true,
            'total_member' => $monthlyTotalMemberCount,
            'total_management_bill_amount' => $monthlyTotalManagementBillAmount,
            'total_storage_bill_amount' => $monthlyTotalStorageBillAmount,
        ]);
    }

    // public function totalMemberCount()
    // {
    //     try {
    //         $users = User::where('type', 'organisation')->get();

    //         $singleUserData = $users->map(function ($user) {
    //             $userId = $user->id;
    //             $date = today();

    //             // Calculate active members from org_member_lists
    //             $orgMembers = DB::table('org_members')
    //                 ->where('org_type_user_id', $userId)
    //                 ->where('is_active', true) // Only count active members
    //                 ->count();

    //             // Calculate active members from org_independent_members
    //             $independentMembers = DB::table('org_independent_members')
    //                 ->where('user_id', $userId)
    //                 ->where('is_active', true) // Only count active members
    //                 ->count();

    //             // Total active members
    //             $totalMembers = $orgMembers + $independentMembers;

    //             // Calculate the price rate per member
    //             $managementDailyPriceRate = 0.03; // Your price rate per member

    //             // Calculate the total bill amount based on the members and price rate
    //             $dayTotalBill = $totalMembers * $managementDailyPriceRate;

    //             // Insert the count into org_member_counts
    //             DB::table('everyday_member_count_and_billings')->updateOrInsert(
    //                 [
    //                     'user_id' => $userId,
    //                     'date' => $date,
    //                 ],
    //                 [
    //                     'day_total_member' => $totalMembers,
    //                     'day_total_bill' => $dayTotalBill,
    //                     'is_active' => true,
    //                 ]
    //             );
    //         });
    //         Log::info('Day total member count and day bill calculation successfully recorded.');
    //         return response()->json([
    //             'message' => 'Day total member count and day bill calculation successfully recorded.',
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $users = User::where('type', 'organisation')->get();

            $userBill = $users->map(function ($user) {
                $userId = $user->id;
                $userName = $user->name;

                $billCalculationResponse = $this->billCalculation($userId);
                $billCalculationData = $billCalculationResponse->getData(true);

                $userCurrencyResponse = $this->getUserCurrency($userId);
                $userCurrencyData = $userCurrencyResponse->getData(true);


                // Get the start and end dates of the previous month
                $startOfPreviousMonth = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                $endOfPreviousMonth = Carbon::now()->subMonth()->endOfMonth()->toDateString();

                ManagementBilling::create([
                    'user_id' =>  $userId,
                    'user_name' => $userName,

                    'service_month' => Carbon::now()->subMonth()->format('F'), // Previous month full name
                    'service_year' => Carbon::now()->subMonth()->format('Y'), // Previous month's year

                    'billing_month' => Carbon::now()->format('F'), // Current month full name
                    'billing_year' => Carbon::now()->format('Y'), // Current month's year

                    'period_start' => $startOfPreviousMonth,
                    'period_end' => $endOfPreviousMonth,

                    //foreign data
                    'total_member' => $billCalculationData['total_member'],
                    'total_management_bill_amount' => $billCalculationData['total_management_bill_amount'],
                    'total_storage_bill_amount' => $billCalculationData['total_storage_bill_amount'],
                    'currency_code' => $userCurrencyData['currency_code'],

                    'bill_status' => 'issued',
                    'admin_note' => 'non-refundable',
                    'is_active' => 1,
                ]);
            });
            return response()->json([
                'status' => true,
                'data' => $userBill,
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
    public function show(ManagementBilling $managementBilling)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ManagementBilling $managementBilling)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ManagementBilling $managementBilling)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ManagementBilling $managementBilling)
    {
        //
    }
}
