<?php

namespace App\Http\Controllers;

use App\Models\OrgMemberCount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrgMemberCountController extends Controller
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
        $userId = $request->user()->id;
        $date = today();

        // Validate the request
        $request->validate([
            'user_id' => 'required|exists:users,id', // Ensure user_id exists in the users table
            'date' => 'required|date', // Ensure date is valid
        ]);
    
    
        // Calculate active members from org_member_lists
        $activeOrgMembers = DB::table('org_member_lists')
            ->where('org_type_user_id', $userId)
            ->where('status', 1) // Only count active members
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date); // Consider end_date only if it exists and is after or equal to the given date
            })
            ->count();
    
        // Calculate active members from org_independent_members
        $activeIndependentMembers = DB::table('org_independent_members')
            ->where('user_id', $userId)
            ->where('is_active', true) // Only count active members
            ->count();
    
        // Total active members
        $totalActiveMembers = $activeOrgMembers + $activeIndependentMembers;
    
        // Insert the count into org_member_counts
        DB::table('org_member_counts')->updateOrInsert(
            [
                'user_id' => $userId,
                'date' => $date,
            ],
            [
                'active_member' => $totalActiveMembers,
                'is_billable' => true,
                'is_active' => true,
            ]
        );
    
        return response()->json([
            'message' => 'Active member count successfully recorded.',
            'data' => [
                'user_id' => $userId,
                'date' => $date,
                'active_member' => $totalActiveMembers,
            ],
        ]);
    }

    public function show(Request $request)
    {
        // Get the authenticated user
        $user_id = $request->user()->id;

        // Define the start date as the first day of the month and end date as today
        $startDate = now()->startOfMonth();
        $endDate = now();

        // Retrieve active member counts for the logged-in user from the start of the month until today
        $orgMemberCounts = OrgMemberCount::where('user_id', $user_id)
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
        $totalActiveMember = 0; // Initialize total active members count
        $priceRate = 0.03; // Define the price rate
        $approximateBill = 0; // Initialize approximate bill amount

        foreach ($dateRange as $date) {
            $memberCount = $orgMemberCounts->firstWhere('date', $date);
            $orgMemberCount = $memberCount ? $memberCount->active_member : 0;
            $isBillable = $memberCount ? $memberCount->is_billable : false;

            // Add to total active members
            $totalActiveMember += $orgMemberCount;

            $fullCounts[] = [
                'date' => $date,
                'active_member' => $orgMemberCount,
                'is_billable' => $isBillable,
            ];
        }

        // Calculate the approximate bill amount until today
        $approximateBill = $totalActiveMember * $priceRate;

        // Return a JSON response with the total active members and approximate bill
        return response()->json([
            'status' => true,
            'data' => $fullCounts,
            'total_active_member' => $totalActiveMember, // Include the total in the response
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
        $previousTotalActiveMember = OrgMemberCount::where('user_id', $user_id)
            ->whereBetween('date', [$firstDayOfPreviousMonth, $lastDayOfPreviousMonth])
            ->sum('active_member'); // Sum the active members for the previous month

        // Set the price rate
        $priceRate = 0.03; // Your price rate per member

        // Calculate the total bill amount based on the active members and price rate
        $previousTotalBillAmount = $previousTotalActiveMember * $priceRate;

        // Fetch daily active member counts for the previous month
        $previousMonthCounts = OrgMemberCount::where('user_id', $user_id)
            ->whereBetween('date', [$firstDayOfPreviousMonth, $lastDayOfPreviousMonth])
            ->orderBy('date')
            ->get(['date', 'active_member', 'is_billable']);

        return response()->json([
            'status' => true,
            'previous_month_total_active_member' => $previousTotalActiveMember,
            'previous_month_total_bill_amount' => $previousTotalBillAmount,
            'previous_month_price_rate' => $priceRate,
            'previous_month_member_count' => $previousMonthCounts,
        ]);
    }

    //     public function getPreviousMonthBillCalculation(Request $request)
    // {
    //     // Get the first and last day of the previous month
    //     $firstDayOfPreviousMonth = now()->subMonth()->startOfMonth();
    //     $lastDayOfPreviousMonth = now()->subMonth()->endOfMonth();

    //     // Fetch total active members for the previous month
    //     $previousTotalActiveMember = OrgMemberCount::whereBetween('created_at', [$firstDayOfPreviousMonth, $lastDayOfPreviousMonth])
    //         ->where('is_billable', true) // Assuming you have a status field to filter active members
    //         ->count();

    //     // Set the price rate
    //     $priceRate = 0.03; // Your price rate per member

    //     // Calculate the total bill amount based on the active members and price rate
    //     $previousTotalBillAmount = $previousTotalActiveMember * $priceRate;
    // // Get the authenticated user
    // $user_id = $request->user()->id;
    //     // Fetch the daily active member counts for the previous month
    //     $previousMonthCounts = OrgMemberCount::selectRaw('DATE(created_at) as date, COUNT(*) as active_member')
    //         ->whereBetween('created_at', [$firstDayOfPreviousMonth, $lastDayOfPreviousMonth])
    //         // ->where('is_billable', true) // Filter for active members
    //         ->where('user_id', $user_id)
    //         // ->groupBy('date')
    //         // ->orderBy('date')
    //         ->get();

    //     return response()->json([
    //         'status' => true,
    //         'previous_total_active_members' => $previousTotalActiveMember,
    //         'previous_total_bill_amount' => $previousTotalBillAmount,
    //         'previous_price_rate' => $priceRate,
    //         'previous_member_counts' => $previousMonthCounts,
    //     ]);
    // }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrgMemberCount $orgMemberCount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrgMemberCount $orgMemberCount)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrgMemberCount $orgMemberCount)
    {
        //
    }
}
