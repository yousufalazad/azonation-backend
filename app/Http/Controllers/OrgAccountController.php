<?php

namespace App\Http\Controllers;

use App\Models\OrgAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrgAccountController extends Controller
{
    // Fetch all transactions for the authenticated user
    public function getTransactions()
    {
        try {
            $transactions = OrgAccount::orderBy('transaction_date', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'data' => $transactions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    // Create a new transaction
    public function createTransaction(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'transaction_date' => 'required|date',
            'transaction_type' => 'required',
            'transaction_amount' => 'required|min:0',
            'description' => 'nullable|string'
        ]);

        try {
            $transaction = OrgAccount::create([
                'user_id' => $validatedData['user_id'],
                'transaction_date' => $validatedData['transaction_date'],
                'transaction_type' => $validatedData['transaction_type'],
                'transaction_amount' => $validatedData['transaction_amount'],
                'description' => $validatedData['description'] ?? null
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Transaction created successfully',
                'data' => $transaction
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], status: 500);
        }
    }

    // Update an existing transaction
    public function updateTransaction(Request $request, $id)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:in,out',
            'transaction_amount' => 'required|numeric|min:0',
            'description' => 'nullable|string'
        ]);

        try {
            $transaction = OrgAccount::where('id', $id)->first();

            if (!$transaction) {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaction not found.'
                ], 404);
            }

            $transaction->update($validatedData);

            return response()->json([
                'status' => true,
                'message' => 'Transaction updated successfully',
                'data' => $transaction
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    // Delete a transaction
    public function deleteTransaction($id)
    {
        try {
            $transaction = OrgAccount::where('id', $id)->first();
            if (!$transaction) {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaction not found.'
                ], 404);
            }
            $transaction->delete();

            return response()->json([
                'status' => true,
                'message' => 'Transaction deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
}