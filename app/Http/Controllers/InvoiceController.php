<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Billing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class InvoiceController extends Controller
{

    public function billingData($userId)
    {

        $billings = Billing::where('user_id', $userId)->get();
        return response()->json([
            'status' => true,
            'data' => $billings,
        ]);
    }

    public function storeBySystem(Request $request)
    {
        try {

            $users = User::get();

            $billingData = $users->map(function ($user) {
                $userId = $user->id;

                // $activeMemberResponse = $this->memberQuantity($userId);
                // $totalActiveMemberData = $activeMemberResponse->getData(true);

                $billingResponse = $this->billingData($userId);
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
                };
            });

            return response()->json([
                'status' => true,
                'message' => 'Billings created successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating billing: ' . $e->getMessage());
        }
    }



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

    // public function indexForSuperAdmin(Request $request)
    // {
    //      // Get the authenticated user
    //      $user_id = $request->user()->id;

    //     // Fetch invoices related to the authenticated user
    //     $invoices = Invoice::where('user_id', $user_id)->get();

    //     // Return the invoices data as a JSON response
    //     return response()->json([
    //         'status' => true,
    //         'data' => $invoices,
    //     ]);
    // }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'invoice_code' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'billing_id' => 'required|',
            'item_name' => 'required|string|max:255',
            'item_description' => 'nullable|string',
            'generated_at' => 'required|date',
            'issued_at' => 'nullable|date',
            'due_at' => 'nullable|date',
            'subtotal_amount' => 'required|numeric|min:0',
            'discount_description' => 'nullable|string|max:255',
            'discount_value' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'credit_applied' => 'nullable|numeric|min:0',
            'total_amount_due' => 'required|numeric|min:0',
            'additional_note' => 'nullable|string',
            'is_published' => 'required|boolean',
            'payment_status' => 'nullable|string|max:255',
            'status_reason' => 'nullable|string|max:255',
            'admin_note' => 'nullable|string',
        ]);

        // Create a new invoice record
        $invoice = new Invoice();
        $invoice->invoice_code = $validatedData['invoice_code'];
        $invoice->user_id = $validatedData['user_id'];
        $invoice->billing_id = $validatedData['billing_id'];
        $invoice->item_name = $validatedData['item_name'];
        $invoice->item_description = $validatedData['item_description'] ?? null;
        $invoice->generated_at = $validatedData['generated_at'];
        $invoice->issued_at = $validatedData['issued_at'] ?? null;
        $invoice->due_at = $validatedData['due_at'] ?? null;
        $invoice->subtotal_amount = $validatedData['subtotal_amount'];
        $invoice->discount_description = $validatedData['discount_description'] ?? null;
        $invoice->discount_value = $validatedData['discount_value'] ?? 0;
        $invoice->tax_amount = $validatedData['tax_amount'] ?? 0;
        $invoice->credit_applied = $validatedData['credit_applied'] ?? 0;
        $invoice->total_amount_due = $validatedData['total_amount_due'];
        $invoice->additional_note = $validatedData['additional_note'] ?? null;
        $invoice->is_published = $validatedData['is_published'];
        $invoice->payment_status = $validatedData['payment_status'] ?? null;
        $invoice->status_reason = $validatedData['status_reason'] ?? null;
        $invoice->admin_note = $validatedData['admin_note'] ?? null;

        // Save the invoice to the database
        $invoice->save();

        // Return a success response
        return response()->json([
            'status' => true,
            'message' => 'Invoice created successfully.',
            'invoice' => $invoice,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($invoiceId)
    {
        // Find the Project by ID
        $invoice = Invoice::find($invoiceId);

        // Check if Project exists
        if (!$invoice) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }

        // Return the Project data
        return response()->json(['status' => true, 'data' => $invoice], 200);
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
    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'invoice_code' => 'required|string|max:255',
            'billing_id' => 'required',
            'item_name' => 'required|string|max:255',
            'item_description' => 'nullable|string',
            'generated_at' => 'required|date',
            'issued_at' => 'nullable|date',
            'due_at' => 'nullable|date',
            'subtotal_amount' => 'required|numeric|min:0',
            'discount_description' => 'nullable|string|max:255',
            'discount_value' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'credit_applied' => 'nullable|numeric|min:0',
            'total_amount_due' => 'required|numeric|min:0',
            'additional_note' => 'nullable|string',
            'is_published' => 'required|boolean',
            'payment_status' => 'required|string|max:50',
            'status_reason' => 'nullable|string|max:255',
            'admin_note' => 'nullable|string',
        ]);

        try {
            // Find the invoice by ID
            $invoice = Invoice::findOrFail($id);

            // Update the invoice with validated data
            $invoice->update([
                'invoice_code' => $validated['invoice_code'],
                'billing_id' => $validated['billing_id'],
                'item_name' => $validated['item_name'],
                'item_description' => $validated['item_description'],
                'generated_at' => $validated['generated_at'],
                'issued_at' => $validated['issued_at'],
                'due_at' => $validated['due_at'],
                'subtotal_amount' => $validated['subtotal_amount'],
                'discount_description' => $validated['discount_description'],
                'discount_value' => $validated['discount_value'],
                'tax_amount' => $validated['tax_amount'],
                'credit_applied' => $validated['credit_applied'],
                'total_amount_due' => $validated['total_amount_due'],
                'additional_note' => $validated['additional_note'],
                'is_published' => $validated['is_published'],
                'payment_status' => $validated['payment_status'],
                'status_reason' => $validated['status_reason'],
                'admin_note' => $validated['admin_note'],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Invoice updated successfully!',
                'invoice' => $invoice,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }

        $invoice->delete();

        return response()->json(['status' => true, 'message' => 'Project deleted successfully']);
    }
}
