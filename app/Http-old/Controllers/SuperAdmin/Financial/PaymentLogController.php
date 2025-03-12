<?php
namespace App\Http\Controllers\SuperAdmin\Financial;
use App\Http\Controllers\Controller;

use App\Models\PaymentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentLogController extends Controller
{
    public function index(Request $request)
    {
        try {
            $paymentLogs = PaymentLog::get();
            return response()->json([
                'status' => true,
                'data' => $paymentLogs,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching packages: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching packages.',
            ], 500);
        }
    }
    public function create() {}
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'gateway' => 'required|string|max:255',
            'transaction_id' => 'nullable|string|max:255',
            'payment_status' => 'required|string|max:255',
            'payment_method' => 'required|string|max:255',
            'currency' => 'required|string|max:10',
            'amount_paid' => 'required|numeric|min:0',
            'exchange_rate' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
            'admin_note' => 'nullable|string',
        ]);
        $paymentLog = new PaymentLog();
        $paymentLog->invoice_id = $validatedData['invoice_id'];
        $paymentLog->user_id = $request->user()->id;
        $paymentLog->user_name = $request->user()->name;
        $paymentLog->gateway = $validatedData['gateway'];
        $paymentLog->transaction_id = $validatedData['transaction_id'] ?? null;
        $paymentLog->payment_status = $validatedData['payment_status'];
        $paymentLog->payment_method = $validatedData['payment_method'];
        $paymentLog->currency = $validatedData['currency'];
        $paymentLog->amount_paid = $validatedData['amount_paid'];
        $paymentLog->exchange_rate = $validatedData['exchange_rate'] ?? null;
        $paymentLog->note = $validatedData['note'] ?? null;
        $paymentLog->admin_note = $validatedData['admin_note'] ?? null;
        $paymentLog->save();
        return response()->json([
            'status' => true,
            'message' => 'Transaction created successfully.',
            'transaction' => $paymentLog,
        ], 201);
    }
    public function show($paymentLogId)
    {
        $paymentLog = PaymentLog::find($paymentLogId);
        if (!$paymentLog) {
            return response()->json(['status' => false, 'message' => 'Payment not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $paymentLog], 200);
    }
    public function edit(PaymentLog $paymentLog) {}
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'gateway' => 'required|string|max:255',
            'transaction_id' => 'nullable|string|max:255',
            'payment_status' => 'required|string|max:255',
            'payment_method' => 'required|string|max:255',
            'currency' => 'required|string|max:10',
            'amount_paid' => 'required|numeric|min:0',
            'exchange_rate' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
            'admin_note' => 'nullable|string',
        ]);
        try {
            $paymentLog = PaymentLog::findOrFail($id);
            $paymentLog->invoice_id = $validatedData['invoice_id'];
            $paymentLog->user_id = $request->user()->id;
            $paymentLog->user_name = $request->user()->name;
            $paymentLog->gateway = $validatedData['gateway'];
            $paymentLog->transaction_id = $validatedData['transaction_id'] ?? null;
            $paymentLog->payment_status = $validatedData['payment_status'];
            $paymentLog->payment_method = $validatedData['payment_method'];
            $paymentLog->currency = $validatedData['currency'];
            $paymentLog->amount_paid = $validatedData['amount_paid'];
            $paymentLog->exchange_rate = $validatedData['exchange_rate'] ?? null;
            $paymentLog->note = $validatedData['note'] ?? null;
            $paymentLog->admin_note = $validatedData['admin_note'] ?? null;
            $paymentLog->save();
            return response()->json([
                'status' => true,
                'message' => 'Transaction updated successfully.',
                'transaction' => $paymentLog,
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
        $paymentLog = PaymentLog::find($id);
        if (!$paymentLog) {
            return response()->json(['status' => false, 'message' => 'Payment not found'], 404);
        }
        $paymentLog->delete();
        return response()->json(['status' => true, 'message' => 'Payment deleted successfully']);
    }
}
