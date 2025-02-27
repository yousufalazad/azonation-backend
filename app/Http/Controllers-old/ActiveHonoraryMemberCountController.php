<?php

namespace App\Http\Controllers;

use App\Models\ActiveHonoraryMemberCount;
use Illuminate\Http\Request;

class ActiveHonoraryMemberCountController extends Controller
{
   
    public function show(Request $request)
    {
        // Get the authenticated user
        $user_id = $request->user()->id;

        // Define the start date as the first day of the month and end date as today
        $startDate = now()->startOfMonth();
        $endDate = now();

        // Retrieve active member counts for the logged-in user from the start of the month until today
        $activeHonoraryMemberCount = ActiveHonoraryMemberCount::where('user_id', $user_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // Create a full list of dates from the start of the month to today
        $dateRange = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lessThanOrEqualTo($endDate)) {
            $dateRange[] = $currentDate->copy()->toDateString();
            $currentDate->addDay();
        }

        // Combine the counts with the date range
        $fullCounts = [];
        $totalActiveHonoraryMember = 0; // Initialize total active members count
        $priceRate = 0.03; // Define the price rate
        $approximateBill = 0; // Initialize approximate bill amount

        foreach ($dateRange as $date) {
            $honoraryMemberCount = $activeHonoraryMemberCount->firstWhere('date', $date);
            $activeHonoraryMemberCount = $honoraryMemberCount ? $honoraryMemberCount->active_honorary_member : 0;
            $isBillable = $honoraryMemberCount ? $honoraryMemberCount->is_billable : false;

            // Add to total active members
            $totalActiveHonoraryMember += $activeHonoraryMemberCount;

            $fullCounts[] = [
                'date' => $date,
                'active_honorary_member' => $activeHonoraryMemberCount,
                'is_billable' => $isBillable,
            ];
        }

        // Calculate the approximate bill amount until today
        $approximateBill = $totalActiveHonoraryMember * $priceRate;

        // Return a JSON response with the total active members and approximate bill
        return response()->json([
            'status' => true,
            'data' => $fullCounts,
            'total_active_honorary_member' => $totalActiveHonoraryMember, // Include the total in the response
            'approximate_bill_honorary' => $approximateBill, // Include the approximate bill
            'price_rate' => $priceRate, // Include the price rate
        ]);
    }

    public function getPreviousMonthBillCalculation(Request $request)
    {
        // Get the authenticated user
        $user_id = $request->user()->id;

        // Get the first and last day of the previous month
        $firstDayOfPreviousMonth = now()->subMonth()->startOfMonth();
        $lastDayOfPreviousMonth = now()->subMonth()->endOfMonth();

        // Fetch total active members for the previous month
        $previousTotalActiveHonoraryMember = ActiveHonoraryMemberCount::where('user_id', $user_id)
            ->whereBetween('date', [$firstDayOfPreviousMonth, $lastDayOfPreviousMonth])
            ->sum('active_honorary_member'); // Sum the active members for the previous month

        // Set the price rate
        $priceRate = 0.03; // Your price rate per member

        // Calculate the total bill amount based on the active members and price rate
        $previousTotalBillAmount = $previousTotalActiveHonoraryMember * $priceRate;

        // Fetch daily active member counts for the previous month
        $previousMonthActiveHonoraryMemberCount = ActiveHonoraryMemberCount::where('user_id', $user_id)
            ->whereBetween('date', [$firstDayOfPreviousMonth, $lastDayOfPreviousMonth])
            ->orderBy('date')
            ->get(['date', 'active_honorary_member', 'is_billable']);

        return response()->json([
            'status' => true,
            'previous_month_total_active_honorary_member' => $previousTotalActiveHonoraryMember,
            'previous_month_total_bill_amount' => $previousTotalBillAmount,
            'previous_month_price_rate' => $priceRate,
            'previous_month_active_honorary_member_count' => $previousMonthActiveHonoraryMemberCount,
        ]);
    }

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ActiveHonoraryMemberCount $activeHonoraryMemberCount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ActiveHonoraryMemberCount $activeHonoraryMemberCount)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ActiveHonoraryMemberCount $activeHonoraryMemberCount)
    {
        //
    }
}
