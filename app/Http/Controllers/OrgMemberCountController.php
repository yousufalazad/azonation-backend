<?php

namespace App\Http\Controllers;

use App\Models\OrgMemberCount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
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
    
   

    /**
     * Display the specified resource.
     */
    public function show(OrgMemberCount $orgMemberCount)
    {
        //
    }

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
