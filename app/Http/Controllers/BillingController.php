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
        $request->validate([
            'billing_code' => 'required',
        ]);

        // Create a new event record associated with the organisation
        Billing::create([
            'billing_code' => $request->billing_code,
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
            'description' => $request->description,
            'billing_address' => $request->billing_address,
            'item_name' => $request->item_name,
            'period_start' => $request->period_start,
            'period_end' => $request->period_end,
            'service_month' => $request->service_month,
            'billing_month' => $request->billing_month,
            'active_member_count' => $request->active_member_count,
            'billable_active_member_count' => $request->billable_active_member_count,
            'member_daily_rate' => $request->member_daily_rate,
            'total_bill_amount' => $request->total_bill_amount,
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'is_active' => $request->is_active,
        ]);        

        // Return a success response
        return response()->json(['status' => true, 'message' => 'Billing created successfully'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show($billingId)
    {
        // Find the Project by ID
        $billing = Billing::find($billingId);

        // Check if Project exists
        if (!$billing) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }

        // Return the Project data
        return response()->json(['status' => true, 'data' => $billing], 200);
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
    public function update(Request $request, $id)
{
    // Validate the incoming request data
    $request->validate([
        'billing_code' => 'required',
    ]);

    // Find the existing billing record by ID
    $billing = Billing::findOrFail($id);

    // Update the billing record with the new data
    $billing->update([
        'billing_code' => $request->billing_code,
        'description' => $request->description,
        'billing_address' => $request->billing_address,
        'item_name' => $request->item_name,
        'period_start' => $request->period_start,
        'period_end' => $request->period_end,
        'service_month' => $request->service_month,
        'billing_month' => $request->billing_month,
        'active_member_count' => $request->active_member_count,
        'billable_active_member_count' => $request->billable_active_member_count,
        'member_daily_rate' => $request->member_daily_rate,
        'total_bill_amount' => $request->total_bill_amount,
        'status' => $request->status,
        'admin_notes' => $request->admin_notes,
        'is_active' => $request->is_active,
    ]);

    // Return a success response
    return response()->json(['status' => true, 'message' => 'Billing updated successfully'], 200);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $id)
    {
        $billing = Billing::find($id);

        if (!$billing) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }

        $billing->delete();

        return response()->json(['status' => true, 'message' => 'Project deleted successfully']);
    }
}
