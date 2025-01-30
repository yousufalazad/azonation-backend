<?php

namespace App\Http\Controllers;

use App\Models\ManagementAndStorageBilling;
use Illuminate\Http\Request;
use App\Models\EverydayMemberCountAndBilling;
use App\Models\EverydayStorageBilling;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ManagementAndStorageBillingController extends Controller
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


    public function store(Request $request)
    {
        Log::info('Store function is being executed');
        try {
            $users = User::where('type', 'organisation')->get();
            Log::info('Fetched users:', ['users' => $users->toArray()]);


            // Map through the users and calculate the bill for each user
            $userBill = $users->map(function ($user) {
                $userId = $user->id;
                $userName = $user->name;

                $billCalculationResponse = $this->billCalculation($userId);
                $billCalculationData = $billCalculationResponse->getData(true);

                $userCurrencyResponse = $this->getUserCurrency($userId);
                $userCurrencyData = $userCurrencyResponse->getData(true);

                Log::info('Bill Calculation Data:', ['userId' => $userId, 'data' => $billCalculationData]);
                Log::info('User Currency Data:', ['userId' => $userId, 'data' => $userCurrencyData]);


                // Get the start and end dates of the previous month
                $startOfPreviousMonth = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                $endOfPreviousMonth = Carbon::now()->subMonth()->endOfMonth()->toDateString();

                // Create a new management billing record
                try {
                    ManagementAndStorageBilling::create([
                        'user_id' => $userId,
                        'user_name' => $userName,
                        'service_month' => Carbon::now()->subMonth()->format('F'),
                        'service_year' => Carbon::now()->subMonth()->format('Y'),
                        'billing_month' => Carbon::now()->format('F'),
                        'billing_year' => Carbon::now()->format('Y'),
                        'period_start' => $startOfPreviousMonth,
                        'period_end' => $endOfPreviousMonth,
                        'total_member' => $billCalculationData['total_member'],
                        'total_management_bill_amount' => $billCalculationData['total_management_bill_amount'],
                        'total_storage_bill_amount' => $billCalculationData['total_storage_bill_amount'],
                        'currency_code' => $userCurrencyData['currency_code'],
                        'bill_status' => 'issued',
                        'admin_note' => 'non-refundable',
                        'is_active' => 1,
                    ]);
                    Log::info("Management billing created for user $userId");
                } catch (\Exception $e) {
                    Log::error("Error creating management billing for user $userId: " . $e->getMessage());
                }
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

   

    public function show(ManagementAndStorageBilling $managementAndStorageBilling)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ManagementAndStorageBilling $managementAndStorageBilling)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ManagementAndStorageBilling $managementAndStorageBilling)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ManagementAndStorageBilling $managementAndStorageBilling)
    {
        //
    }
}
