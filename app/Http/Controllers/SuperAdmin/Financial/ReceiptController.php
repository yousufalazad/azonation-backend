<?php
namespace App\Http\Controllers\SuperAdmin\Financial;
use App\Http\Controllers\Controller;

use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReceiptController extends Controller
{
    public function orgIndex()
    {
        try {
            $receipts = Receipt::all();
            return response()->json([
                'status' => true,
                'data' => $receipts,
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
        $validated = $request->validate([
            'receipt_code' => 'required|string|max:255|unique:receipts,receipt_code',
            'invoice_id' => 'required|exists:invoices,id',
            'user_id' => 'required|exists:users,id',
            'amount_received' => 'required|numeric|min:0',
            'gateway_type' => 'required|string',
            'transaction_reference' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'note' => 'nullable|string',
            'status' => 'nullable',
            'admin_note' => 'nullable|string',
            'is_published' => 'boolean',
        ]);
        try {
            $receipt = Receipt::create([
                'receipt_code' => $validated['receipt_code'],
                'invoice_id' => $validated['invoice_id'],
                'user_id' => $validated['user_id'],
                'amount_received' => $validated['amount_received'],
                'gateway_type' => $validated['gateway_type'],
                'transaction_reference' => $validated['transaction_reference'],
                'payment_date' => $validated['payment_date'],
                'note' => $validated['note'] ?? null,
                'status' => $validated['status'],
                'admin_note' => $validated['admin_note'] ?? null,
                'is_published' => $validated['is_published'] ?? false,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Receipt created successfully.',
                'data' => $receipt,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating receipt: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }
    public function show($id)
    {
        try {
            $receipt = Receipt::findOrFail($id);
            return response()->json([
                'status' => true,
                'message' => 'Receipt retrieved successfully.',
                'data' => $receipt,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving receipt: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }
    public function edit(Receipt $receipt) {}
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'receipt_code' => 'required|string|max:255|unique:receipts,receipt_code,' . $id,
            'invoice_id' => 'required|exists:invoices,id',
            'user_id' => 'required|exists:users,id',
            'amount_received' => 'required|numeric|min:0',
            'gateway_type' => 'required|string',
            'transaction_reference' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'note' => 'nullable|string',
            'status' => 'nullable',
            'admin_note' => 'nullable|string',
            'is_published' => 'boolean',
        ]);
        try {
            $receipt = Receipt::findOrFail($id);
            $receipt->update([
                'receipt_code' => $validated['receipt_code'],
                'invoice_id' => $validated['invoice_id'],
                'user_id' => $validated['user_id'],
                'amount_received' => $validated['amount_received'],
                'gateway_type' => $validated['gateway_type'],
                'transaction_reference' => $validated['transaction_reference'],
                'payment_date' => $validated['payment_date'],
                'note' => $validated['note'] ?? null,
                'status' => $validated['status'],
                'admin_note' => $validated['admin_note'] ?? null,
                'is_published' => $validated['is_published'] ?? false,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Receipt updated successfully.',
                'data' => $receipt,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating receipt: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $receipt = Receipt::findOrFail($id);
            $receipt->delete();
            return response()->json([
                'status' => true,
                'message' => 'Receipt deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting receipt: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }
}
