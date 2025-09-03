<?php

namespace App\Http\Controllers\Org\Membership;

use App\Http\Controllers\Controller;

use App\Models\MembershipTerminationReason;
use Illuminate\Http\Request;

class MembershipTerminationReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $membershipTerminationReasons = MembershipTerminationReason::where('is_active', 1)->get();
        return response()->json([
            'status' => true,
            'data' => $membershipTerminationReasons
        ]);
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
     * Display the specified resource.
     */
    public function show(MembershipTerminationReason $membershipTerminationReason)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MembershipTerminationReason $membershipTerminationReason)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MembershipTerminationReason $membershipTerminationReason)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MembershipTerminationReason $membershipTerminationReason)
    {
        //
    }
}
