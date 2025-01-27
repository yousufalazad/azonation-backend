<?php

namespace App\Http\Controllers;

use App\Models\EverydayMemberCountAndBilling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EverydayMemberCountAndBillingController extends Controller
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
        $orgMembers = DB::table('org_members')
            ->where('org_type_user_id', $userId)
            ->where('is_active', true) // Only count active members
            ->count();

            // ->where(function ($query) use ($date) {
            //     $query->whereNull('membership_start_date')
            //         ->orWhere('membership_start_date', '>=', $date); // Consider end_date only if it exists and is after or equal to the given date
            // })
    
        // Calculate active members from org_independent_members
        $independentMembers = DB::table('org_independent_members')
            ->where('user_id', $userId)
            ->where('is_active', true) // Only count active members
            ->count();
    
        // Total active members
        $totalMembers = $orgMembers + $independentMembers;

        // Calculate the price rate per member
        $managementPriceRate = 0.03; // Your price rate per member

        // Calculate the total bill amount based on the members and price rate
        $dayTotalBill = $totalMembers * $managementPriceRate;
    
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
    
        return response()->json([
            'message' => 'Day total member count and day bill calculation successfully recorded.',
            'data' => [
                'user_id' => $userId,
                'date' => $date,
                'day_total_member' => $totalMembers,
                'day_total_bill' => $dayTotalBill,
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(EverydayMemberCountAndBilling $everydayMemberCountAndBilling)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EverydayMemberCountAndBilling $everydayMemberCountAndBilling)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EverydayMemberCountAndBilling $everydayMemberCountAndBilling)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EverydayMemberCountAndBilling $everydayMemberCountAndBilling)
    {
        //
    }
}
