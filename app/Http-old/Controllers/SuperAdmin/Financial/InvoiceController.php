<?php
namespace App\Http\Controllers\SuperAdmin\Financial;
use App\Http\Controllers\Controller;

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
            $user_id = $request->user()->id;
            $invoices = Invoice::where('user_id', $user_id)->get();
            return response()->json([
                'status' => true,
                'data' => $invoices,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching packages: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching packages.',
            ], 500);
        }
    }
    public function indexForSuperadmin(Request $request)
    {
        $invoices = Invoice::get();
        return response()->json([
            'status' => true,
            'data' => $invoices,
        ]);
    }
    public function create() {}
    public function managementAndStorageInvoice(): void
    {
        Log::info('invoice function started');
        $orders = Order::whereNotIn('id', function ($query) {
            $query->select('order_id')->from('invoices');
        })->get();
        Log::info('Found ' . $orders->count() . ' orders to invoice');
        foreach ($orders as $order) {
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
    public function X_show($invoiceId)
    {
        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $invoice], 200);
    }
    public function show($invoiceId)
    {
        $invoice = Invoice::with([
            'order',
            'order.orderDetail',
            'order.orderItems',
            'order.user',
        ])->find($invoiceId);
        if (!$invoice) {
            return response()->json(['status' => false, 'message' => 'Invoice not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $invoice], 200);
    }
    public function edit(Invoice $invoice) {}
    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $request->validate([
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
