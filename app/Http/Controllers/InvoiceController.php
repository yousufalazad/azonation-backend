<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        try {
            //Get the authenticated user
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

    public function managementAndStorageInvoice() : void
    {
        // Get all users with active storage subscriptions
        // $users = User::with('storageSubscription')->whereHas('storageSubscription', function ($query) {
        //     $query->where('is_active', true);
        // })->get();

        // Process billing for each user
        // foreach ($users as $user) {
        //     $billingData = $this->getUserStorageDailyPriceRate($user->id);
        //     $this->storeBillingRecord($billingData);
        // }

        // return response()->json(['status' => true, 'message' => 'Billing records generated successfully.'], 200);
        
    }

    public function store(Request $request)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'billing_address' => 'nullable|string|max:255',
            'billing_id' => 'required|exists:billings,id',
            'billing_code' => 'nullable',
            'item_name' => 'nullable|string|max:255',
            'item_description' => 'nullable|string',
            'generated_at' => 'nullable|date',
            'issued_at' => 'nullable|date',
            'due_at' => 'nullable|date',
            'total_active_member' => 'nullable|integer|min:0',
            'total_active_honorary_member' => 'nullable|integer|min:0',
            'total_billable_active_member' => 'nullable|integer|min:0',
            'subscribed_package_name' => 'nullable|string|max:255',
            'price_rate' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'subtotal' => 'nullable|numeric|min:0',
            'discount_title' => 'nullable|string|max:255',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'balance' => 'nullable|numeric|min:0',
            'invoice_note' => 'nullable|string',
            'invoice_status' => 'nullable|string|max:255',
            'credit_applied' => 'nullable|numeric|min:0',
            'is_published' => 'required|boolean',
            'payment_status' => 'nullable|string|max:255',
            'payment_status_reason' => 'nullable|string|max:255',
            'admin_note' => 'nullable|string',
        ]);

        // Create a new invoice record
        $invoice = new Invoice();
        // $invoice->invoice_code = $validatedData['invoice_code'];
        $invoice->user_id = $request->user()->id;
        $invoice->user_name = $request->user()->name;
        $invoice->billing_address = $validatedData['billing_address'] ?? null;
        $invoice->billing_id = $validatedData['billing_id'];
        $invoice->billing_code = $validatedData['billing_code'] ?? null;
        $invoice->item_name = $validatedData['item_name'];
        $invoice->item_description = $validatedData['item_description'] ?? null;
        $invoice->generated_at = $validatedData['generated_at'];
        $invoice->issued_at = $validatedData['issued_at'] ?? null;
        $invoice->due_at = $validatedData['due_at'] ?? null;
        $invoice->total_active_member = $validatedData['total_active_member'] ?? 0;
        $invoice->total_active_honorary_member = $validatedData['total_active_honorary_member'] ?? 0;
        $invoice->total_billable_active_member = $validatedData['total_billable_active_member'] ?? 0;
        $invoice->subscribed_package_name = $validatedData['subscribed_package_name'] ?? null;
        $invoice->price_rate = $validatedData['price_rate'] ?? 0;
        $invoice->currency = $validatedData['currency'] ?? null;
        $invoice->subtotal = $validatedData['subtotal'];
        $invoice->discount_title = $validatedData['discount_title'] ?? null;
        $invoice->discount = $validatedData['discount'] ?? 0;
        $invoice->tax = $validatedData['tax'] ?? 0;
        $invoice->balance = $validatedData['balance'] ?? 0;
        $invoice->invoice_note = $validatedData['invoice_note'] ?? null;
        $invoice->invoice_status = $validatedData['invoice_status'] ?? null;
        $invoice->credit_applied = $validatedData['credit_applied'] ?? 0;
        $invoice->is_published = $validatedData['is_published'];
        $invoice->payment_status = $validatedData['payment_status'] ?? null;
        $invoice->payment_status_reason = $validatedData['payment_status_reason'] ?? null;
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
        $validatedData = $request->validate([
            'billing_address' => 'nullable|string|max:255',
            'billing_id' => 'required|exists:billings,id',
            'billing_code' => 'nullable',
            'item_name' => 'nullable|string|max:255',
            'item_description' => 'nullable|string',
            'generated_at' => 'nullable|date',
            'issued_at' => 'nullable|date',
            'due_at' => 'nullable|date',
            'total_active_member' => 'nullable|integer|min:0',
            'total_active_honorary_member' => 'nullable|integer|min:0',
            'total_billable_active_member' => 'nullable|integer|min:0',
            'subscribed_package_name' => 'nullable|string|max:255',
            'price_rate' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'subtotal' => 'nullable|numeric|min:0',
            'discount_title' => 'nullable|string|max:255',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'balance' => 'nullable|numeric|min:0',
            'invoice_note' => 'nullable|string',
            'invoice_status' => 'nullable|string|max:255',
            'credit_applied' => 'nullable|numeric|min:0',
            'is_published' => 'required|boolean',
            'payment_status' => 'nullable|string|max:255',
            'payment_status_reason' => 'nullable|string|max:255',
            'admin_note' => 'nullable|string',
        ]);

        try {
            // Find the invoice by ID
            $invoice = Invoice::findOrFail($id);

            // Update the invoice with validated data
            $invoice->user_id = $request->user()->id;
            $invoice->user_name = $request->user()->name;
            $invoice->billing_address = $validatedData['billing_address'] ?? null;
            $invoice->billing_id = $validatedData['billing_id'];
            $invoice->billing_code = $validatedData['billing_code'] ?? null;
            $invoice->item_name = $validatedData['item_name'];
            $invoice->item_description = $validatedData['item_description'] ?? null;
            $invoice->generated_at = $validatedData['generated_at'];
            $invoice->issued_at = $validatedData['issued_at'] ?? null;
            $invoice->due_at = $validatedData['due_at'] ?? null;
            $invoice->total_active_member = $validatedData['total_active_member'] ?? 0;
            $invoice->total_active_honorary_member = $validatedData['total_active_honorary_member'] ?? 0;
            $invoice->total_billable_active_member = $validatedData['total_billable_active_member'] ?? 0;
            $invoice->subscribed_package_name = $validatedData['subscribed_package_name'] ?? null;
            $invoice->price_rate = $validatedData['price_rate'] ?? 0;
            $invoice->currency = $validatedData['currency'] ?? null;
            $invoice->subtotal = $validatedData['subtotal'];
            $invoice->discount_title = $validatedData['discount_title'] ?? null;
            $invoice->discount = $validatedData['discount'] ?? 0;
            $invoice->tax = $validatedData['tax'] ?? 0;
            $invoice->balance = $validatedData['balance'] ?? 0;
            $invoice->invoice_note = $validatedData['invoice_note'] ?? null;
            $invoice->invoice_status = $validatedData['invoice_status'] ?? null;
            $invoice->credit_applied = $validatedData['credit_applied'] ?? 0;
            $invoice->is_published = $validatedData['is_published'];
            $invoice->payment_status = $validatedData['payment_status'] ?? null;
            $invoice->payment_status_reason = $validatedData['payment_status_reason'] ?? null;
            $invoice->admin_note = $validatedData['admin_note'] ?? null;

            // Save the invoice to the database
            $invoice->save();

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
