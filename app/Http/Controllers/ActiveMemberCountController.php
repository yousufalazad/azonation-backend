<?php

namespace App\Http\Controllers;

use App\Models\ActiveMemberCount;
use Illuminate\Http\Request;

class ActiveMemberCountController extends Controller
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function show(Request $request)
    {
        // Get the authenticated user
        $user_id = $request->user()->id;

        // Define the start date as the first day of the month and end date as today
        $startDate = now()->startOfMonth();
        $endDate = now();

        // Retrieve active member counts for the logged-in user from the start of the month until today
        $activeMemberCounts = ActiveMemberCount::where('user_id', $user_id)
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
        $totalActiveMembers = 0; // Initialize total active members count
        $priceRate = 0.03; // Define the price rate
        $approximateBill = 0; // Initialize approximate bill amount

        foreach ($dateRange as $date) {
            $memberCount = $activeMemberCounts->firstWhere('date', $date);
            $activeMemberCount = $memberCount ? $memberCount->active_member : 0;
            $isBillable = $memberCount ? $memberCount->is_billable : false;

            // Add to total active members
            $totalActiveMembers += $activeMemberCount;

            $fullCounts[] = [
                'date' => $date,
                'active_member' => $activeMemberCount,
                'is_billable' => $isBillable,
            ];
        }

        // Calculate the approximate bill amount until today
        $approximateBill = $totalActiveMembers * $priceRate;

        // Return a JSON response with the total active members and approximate bill
        return response()->json([
            'status' => true,
            'data' => $fullCounts,
            'total_active_members' => $totalActiveMembers, // Include the total in the response
            'approximate_bill' => $approximateBill, // Include the approximate bill
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
        $previousTotalActiveMembers = ActiveMemberCount::where('user_id', $user_id)
            ->whereBetween('date', [$firstDayOfPreviousMonth, $lastDayOfPreviousMonth])
            ->sum('active_member'); // Sum the active members for the previous month

        // Set the price rate
        $priceRate = 0.03; // Your price rate per member

        // Calculate the total bill amount based on the active members and price rate
        $previousTotalBillAmount = $previousTotalActiveMembers * $priceRate;

        // Fetch daily active member counts for the previous month
        $previousMonthCounts = ActiveMemberCount::where('user_id', $user_id)
            ->whereBetween('date', [$firstDayOfPreviousMonth, $lastDayOfPreviousMonth])
            ->orderBy('date')
            ->get(['date', 'active_member', 'is_billable']);

        return response()->json([
            'status' => true,
            'previous_total_active_members' => $previousTotalActiveMembers,
            'previous_total_bill_amount' => $previousTotalBillAmount,
            'previous_price_rate' => $priceRate,
            'previous_member_counts' => $previousMonthCounts,
        ]);
    }

    //     public function getPreviousMonthBillCalculation(Request $request)
    // {
    //     // Get the first and last day of the previous month
    //     $firstDayOfPreviousMonth = now()->subMonth()->startOfMonth();
    //     $lastDayOfPreviousMonth = now()->subMonth()->endOfMonth();

    //     // Fetch total active members for the previous month
    //     $previousTotalActiveMembers = ActiveMemberCount::whereBetween('created_at', [$firstDayOfPreviousMonth, $lastDayOfPreviousMonth])
    //         ->where('is_billable', true) // Assuming you have a status field to filter active members
    //         ->count();

    //     // Set the price rate
    //     $priceRate = 0.03; // Your price rate per member

    //     // Calculate the total bill amount based on the active members and price rate
    //     $previousTotalBillAmount = $previousTotalActiveMembers * $priceRate;
    // // Get the authenticated user
    // $user_id = $request->user()->id;
    //     // Fetch the daily active member counts for the previous month
    //     $previousMonthCounts = ActiveMemberCount::selectRaw('DATE(created_at) as date, COUNT(*) as active_member')
    //         ->whereBetween('created_at', [$firstDayOfPreviousMonth, $lastDayOfPreviousMonth])
    //         // ->where('is_billable', true) // Filter for active members
    //         ->where('user_id', $user_id)
    //         // ->groupBy('date')
    //         // ->orderBy('date')
    //         ->get();

    //     return response()->json([
    //         'status' => true,
    //         'previous_total_active_members' => $previousTotalActiveMembers,
    //         'previous_total_bill_amount' => $previousTotalBillAmount,
    //         'previous_price_rate' => $priceRate,
    //         'previous_member_counts' => $previousMonthCounts,
    //     ]);
    // }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ActiveMemberCount $activeMemberCount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ActiveMemberCount $activeMemberCount)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ActiveMemberCount $activeMemberCount)
    {
        //
    }
}
