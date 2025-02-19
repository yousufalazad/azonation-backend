<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderDetails;
use Carbon\Carbon;


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

    public function indexForSuperadmin(Request $request)
    {
        $invoices = Invoice::get();

        // Return the invoices data as a JSON response
        return response()->json([
            'status' => true,
            'data' => $invoices,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function managementAndStorageInvoice(): void
    {
        Log::info('invoice function started');
        // Fetch all orders that haven't been invoiced yet
        $orders = Order::whereNotIn('id', function ($query) {
            $query->select('order_id')->from('invoices');
        })->get();
        Log::info('Found ' . $orders->count() . ' orders to invoice');

        foreach ($orders as $order) {
            // Create invoice
            Invoice::create([
                'order_id'       => $order->id,
                'billing_code'  => $order->billing_code,
                'order_code'     => $order->order_code,
                'user_id'        => $order->user_id,
                'user_name'      => $order->user_name,

                'description'    => 'Invoice for order: ' . $order->order_code,

                'total_amount'   => $order->total_amount,
                'amount_paid'    => 0,
                'balance_due'    => $order->total_amount - 0,

                'currency_code'  => $order->currency_code,
                'generate_date'  => Carbon::now(),
                'issue_date'     => Carbon::now(),
                // 'due_date'       => Carbon::now()->addDays(25),
                'due_date'       => Carbon::now()->endOfMonth(),
                'terms'          => 'Payment due in 30 days',
                'invoice_note'   => 'Non-refundable',
                'is_published'   => true,
                'invoice_status' => 'issued',
                'payment_status' => 'unpaid',
                'admin_note'     => null,
                'is_active'      => true,
            ]);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            // 'invoice_code'   => 'required|string|unique:invoices,invoice_code',
            'billing_code'   => 'nullable|string',
            'order_code'     => 'nullable|string',
            'order_id'       => 'nullable|integer',
            'user_id'        => 'required|integer|exists:users,id',
            'user_name'      => 'required|string',
            'description'    => 'nullable|string',
            'total_amount'   => 'required|numeric',
            'amount_paid'    => 'required|numeric',
            'balance_due'    => 'required|numeric',
            'currency_code'  => 'required|string',
            'generate_date'  => 'required|date',
            'issue_date'     => 'required|date',
            'due_date'       => 'nullable|date',
            'terms'          => 'nullable|string',
            'invoice_note'   => 'nullable|string',
            'is_published'   => 'boolean',
            'invoice_status' => 'required|string',
            'payment_status' => 'required|string',
            'admin_note'     => 'nullable|string',
            'is_active'      => 'boolean',
        ]);

        $invoice = Invoice::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Invoice created successfully!',
            'invoice' => $invoice
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function X_show($invoiceId)
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

    public function show($invoiceId)
    {
        // Fetch invoice with related order, order items, and order details
        $invoice = Invoice::with([
            'order',
            'order.orderDetail',
            'order.orderItems',
            'order.user',
        ])->find($invoiceId);

        // Check if invoice exists
        if (!$invoice) {
            return response()->json(['status' => false, 'message' => 'Invoice not found'], 404);
        }

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
        $invoice = Invoice::findOrFail($id);

        $request->validate([
            // 'invoice_code'   => 'required|string|unique:invoices,invoice_code,' . $id,
            'billing_code'   => 'nullable|string',
            'order_code'     => 'nullable|string',
            'order_id'       => 'nullable|integer',
            'user_id'        => 'required|integer|exists:users,id',
            'user_name'      => 'required|string',
            'description'    => 'nullable|string',
            'total_amount'   => 'required|numeric',
            'amount_paid'    => 'required|numeric',
            'balance_due'    => 'required|numeric',
            'currency_code'  => 'required|string',
            'generate_date'  => 'required|date',
            'issue_date'     => 'required|date',
            'due_date'       => 'nullable|date',
            'terms'          => 'nullable|string',
            'invoice_note'   => 'nullable|string',
            'is_published'   => 'boolean',
            'invoice_status' => 'required|string',
            'payment_status' => 'required|string',
            'admin_note'     => 'nullable|string',
            'is_active'      => 'boolean',
        ]);

        $invoice->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Invoice updated successfully!',
            'invoice' => $invoice
        ], 200);
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
