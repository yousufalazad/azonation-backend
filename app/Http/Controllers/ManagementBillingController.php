<?php

namespace App\Http\Controllers;

use App\Models\ManagementBilling;
use App\Models\EverydayMemberCountAndBilling;
use App\Models\EverydayStorageBilling;
use Illuminate\Http\Request;
use Carbon\Carbon;


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
    public function getCurrency(){
        $userCurrency = 
    }

    public function monthlyTotalMemberTotalManagementBillTotalStorageBillCalculation($userId)
    {
        // Get the start and end dates of the previous month
        $startOfPreviousMonth = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $endOfPreviousMonth = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        $monthlyTotalMemberCount = EverydayMemberCountAndBilling::where('user_id', $userId)
            ->whereBetween('date', [$startOfPreviousMonth, $endOfPreviousMonth])
            ->get();

        $monthlyTotalManagementBillAmount = EverydayMemberCountAndBilling::where('user_id', $userId)
            ->whereBetween('date', [$startOfPreviousMonth, $endOfPreviousMonth])
            ->get();

        $monthlyTotalStorageBillAmount = EverydayStorageBilling::where('user_id', $userId)
            ->whereBetween('date', [$startOfPreviousMonth, $endOfPreviousMonth])
            ->get();

        return response()->json([
            'status' => true,
            'total_active_member' => $activeMemberCount->sum('active_member'),
            'total_active_honorary_member' => $activeHonoraryMemberCount->sum('active_honorary_member'), //per day 5 ta kore bad dite hobe
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'service_month' => $startDate->format('F'), // Service month name
            'billing_month' => $startDate->addMonth()->format('F'), // Billing month
            'service_year' => $startDate->format('Y'), // Service year
            'billing_year' => $startDate->addMonth()->format('Y'), // Billing year
        ]);
    }

    public function totalMemberCount()
    {
        try {
            $users = User::where('type', 'organisation')->get();

            $singleUserData = $users->map(function ($user) {
                $userId = $user->id;
                $date = today();

                // Calculate active members from org_member_lists
                $orgMembers = DB::table('org_members')
                    ->where('org_type_user_id', $userId)
                    ->where('is_active', true) // Only count active members
                    ->count();

                // Calculate active members from org_independent_members
                $independentMembers = DB::table('org_independent_members')
                    ->where('user_id', $userId)
                    ->where('is_active', true) // Only count active members
                    ->count();

                // Total active members
                $totalMembers = $orgMembers + $independentMembers;

                // Calculate the price rate per member
                $managementDailyPriceRate = 0.03; // Your price rate per member

                // Calculate the total bill amount based on the members and price rate
                $dayTotalBill = $totalMembers * $managementDailyPriceRate;

                // Insert the count into org_member_counts
                DB::table('everyday_member_count_and_billings')->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'date' => $date,
                    ],
                    [
                        'day_total_member' => $totalMembers,
                        'day_total_bill' => $dayTotalBill,
                        'is_active' => true,
                    ]
                );
            });
            Log::info('Day total member count and day bill calculation successfully recorded.');
            return response()->json([
                'message' => 'Day total member count and day bill calculation successfully recorded.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Get the start and end dates of the previous month
        $startOfPreviousMonth = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $endOfPreviousMonth = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        ManagementBilling::create([
            'user_id' => 1,
            'user_name' => 'Azon',

            'service_month' => Carbon::now()->subMonth()->format('F'), // Previous month full name
            'service_year' => Carbon::now()->subMonth()->format('Y'), // Previous month's year

            'billing_month' => Carbon::now()->format('F'), // Current month full name
            'billing_year' => Carbon::now()->format('Y'), // Current month's year

            'period_start' => $startOfPreviousMonth,
            'period_end' => $endOfPreviousMonth,

            'total_member' => $totalMemberForThePeriod['total_member'],
            'total_management_bill_amount' => $totalManagementBillForThePeriod['total_management_bill_amount'],
            'total_storage_bill_amount' => $totalStorageBillForThePeriod['total_storage_bill_amount'],

            'currency' => $currencyOnPeriodTime['currency'],
            'bill_status' => 'issued',
            'admin_note' => 'non-refundable',
            'is_active' => 1,
        ]);
    }
    //      'user_id',
    //     'user_name',        
    //     'service_month',
    //     'billing_month',
    //     'service_year',
    //     'billing_year',
    //     'period_start',
    //     'period_end',

    //     'total_member',
    //     'total_management_bill_amount',
    //     'total_storage_bill_amount',
    //     'currency',
    //     'bill_status',
    //     'admin_note',
    //     'is_active'

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
