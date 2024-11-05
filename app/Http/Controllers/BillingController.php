<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class BillingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
           // Get the authenticated user
         $user_id = $request->user()->id;

         // Fetch billing related to the authenticated user
         $billingList = Billing::where('user_id', $user_id)->get();
 
         // Return the billing data as a JSON response
         return response()->json([
             'status' => true,
             'data' => $billingList,
         ]);
        } catch (\Exception $e) {
            // Log the exception for debugging
            Log::error('Error fetching packages: ' . $e->getMessage());

            // Return JSON response with error status
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching packages.',
            ], 500);
        }
    }

    // 'user_id',
    //     'user_name',
    //     'billing_address',
    //     'item_name',
    //     'period_start',
    //     'period_end',
    //     'active_member_count',
    //     'billable_active_member_count',
    //     'member_daily_rate',
    //     'total_bill_amount',
    //     'status',
    //     'admin_notes',
    //     'is_active'

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
    public function show(Billing $billing)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Billing $billing)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Billing $billing)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Billing $billing)
    {
        //
    }
}
