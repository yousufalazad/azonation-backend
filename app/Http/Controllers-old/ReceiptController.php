<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            //$user_id = $request->user()->id; // Retrieve the authenticated user's ID
            $receipts = Receipt::all();
            // Return JSON response with status and data
            return response()->json([
                'status' => true,
                'data' => $receipts,
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
        // Validate incoming request
        $validated = $request->validate([
            'receipt_code' => 'required|string|max:255|unique:receipts,receipt_code',
            'invoice_id' => 'required|exists:invoices,id',
            'user_id' => 'required|exists:users,id',
            'amount_received' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'transaction_reference' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'note' => 'nullable|string',
            'status' => 'nullable',
            'admin_note' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        try {
            // Create receipt
            $receipt = Receipt::create([
                'receipt_code' => $validated['receipt_code'],
                'invoice_id' => $validated['invoice_id'],
                'user_id' => $validated['user_id'],
                'amount_received' => $validated['amount_received'],
                'payment_method' => $validated['payment_method'],
                'transaction_reference' => $validated['transaction_reference'],
                'payment_date' => $validated['payment_date'],
                'note' => $validated['note'] ?? null,
                'status' => $validated['status'],
                'admin_note' => $validated['admin_note'] ?? null,
                'is_published' => $validated['is_published'] ?? false,
            ]);

            // Return success response
            return response()->json([
                'status' => true,
                'message' => 'Receipt created successfully.',
                'data' => $receipt,
            ], 201);
        } catch (\Exception $e) {
            // Log error and return error response
            Log::error('Error creating receipt: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            // Find receipt by ID
            $receipt = Receipt::findOrFail($id);

            // Return success response with receipt data
            return response()->json([
                'status' => true,
                'message' => 'Receipt retrieved successfully.',
                'data' => $receipt,
            ], 200);
        } catch (\Exception $e) {
            // Log error and return error response
            Log::error('Error retrieving receipt: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Receipt $receipt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validate incoming request
        $validated = $request->validate([
            'receipt_code' => 'required|string|max:255|unique:receipts,receipt_code,' . $id,
            'invoice_id' => 'required|exists:invoices,id',
            'user_id' => 'required|exists:users,id',
            'amount_received' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'transaction_reference' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'note' => 'nullable|string',
            'status' => 'nullable',
            'admin_note' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        try {
            // Find receipt by ID
            $receipt = Receipt::findOrFail($id);

            // Update receipt details
            $receipt->update([
                'receipt_code' => $validated['receipt_code'],
                'invoice_id' => $validated['invoice_id'],
                'user_id' => $validated['user_id'],
                'amount_received' => $validated['amount_received'],
                'payment_method' => $validated['payment_method'],
                'transaction_reference' => $validated['transaction_reference'],
                'payment_date' => $validated['payment_date'],
                'note' => $validated['note'] ?? null,
                'status' => $validated['status'],
                'admin_note' => $validated['admin_note'] ?? null,
                'is_published' => $validated['is_published'] ?? false,
            ]);

            // Return success response
            return response()->json([
                'status' => true,
                'message' => 'Receipt updated successfully.',
                'data' => $receipt,
            ], 200);
        } catch (\Exception $e) {
            // Log error and return error response
            Log::error('Error updating receipt: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Find receipt by ID
            $receipt = Receipt::findOrFail($id);

            // Delete the receipt
            $receipt->delete();

            // Return success response
            return response()->json([
                'status' => true,
                'message' => 'Receipt deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            // Log error and return error response
            Log::error('Error deleting receipt: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }
}
