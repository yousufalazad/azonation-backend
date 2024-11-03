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

        // Define the start and end date of the current month
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        // Retrieve active member counts for the logged-in user within the current month
        $activeMemberCounts = ActiveMemberCount::where('user_id', $user_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // Create a full list of dates for the current month
        $dateRange = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lessThanOrEqualTo($endDate)) {
            $dateRange[] = $currentDate->copy()->toDateString();
            $currentDate->addDay();
        }

        // Combine the counts with the date range
        $fullCounts = [];
        $totalActiveMembers = 0; // Initialize total active members count

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

        // Return a JSON response with the total active members
        return response()->json([
            'status' => true,
            'data' => $fullCounts,
            'total_active_members' => $totalActiveMembers, // Include the total in the response
        ]);
    }


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
