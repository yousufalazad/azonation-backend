<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Billing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Get the authenticated user
            $user_id = $request->user()->id;

            // Fetch invoices related to the authenticated user
            $invoices = Invoice::where('user_id', $user_id)->get();

            // Return the invoices data as a JSON response
            return response()->json([
                'status' => true,
                'data' => $invoices,
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

    public function indexForSuperAdmin(Request $request)
    {
        $invoices = Invoice::get();
        return response()->json([
            'status' => true,
            'data' => $invoices,
        ]);
    }

    public function billingData() {
        
        $billings = Billing::get();
        return response()->json([
            'status' => true,
            'data' => $billings,
        ]);
    }

    public function storeBySystem(Request $request)
    {
        try {
            $billingResponse = $this->billingData();
            $billingData = $billingResponse->getData(true); // Converts JSON response to an associative array

            foreach ($billingData['data'] as $billing) {
                Invoice::create([
                    'billing_code' => $billing['billing_code'],
                    'user_id' => $billing['user_id'],
                    'user_name' => $billing['user_name'],
                    'item_name' => 'Azonation subscription fee',
                    'item_description' => 'abc',
                    'total_active_member' => $billing['total_active_member'],
                    'total_honorary_member' => $billing['total_honorary_member'],
                    'total_billable_active_member' => $billing['total_billable_active_member'],
                    'generated_at' => now(),
                    'issued_at' => now(),
                    'due_at' => now()->endOfMonth(),
                    'subtotal' => $billing['user_name'],
                    'discount_title' => 'abc',
                    'discount' => 2,
                    'tax' => 1,
                    'credit_applied' => 1.5,
                    'balance' => 10,
                    'invoice_note' => 'abc',
                    'is_published' => true,
                    'invoice_status' => 'issued',
                    'payment_status' => 'unpaid',
                    'payment_status_reason' => 'Reason for the current payment status',
                    'admin_note' => 'admin_note'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error generating billing: ' . $e->getMessage());
        }
    }

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
    public function show(Invoice $invoice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }
}
