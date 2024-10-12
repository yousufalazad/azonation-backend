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
    // public function createTransaction(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //         //'account_fund_id' => 'required|exists:account_funds,id',
    //         //'transaction_id' => 'required|alphanumeric',
    //         'transaction_date' => 'required|date',
    //         'transaction_type' => 'required|in:income,expense',
    //         'transaction_amount' => 'required|numeric|min:0',
    //         'description' => 'required|string|max:255'
    //     ]);

    //     try {
    //         $transaction = OrgAccount::create([
    //             'user_id' => $validatedData['user_id'],
    //             //'transaction_id' => $validatedData['transaction_id'],
    //             //'account_fund_id' => $validatedData['account_fund_id'],
    //             'transaction_date' => $validatedData['transaction_date'],
    //             'transaction_type' => $validatedData['transaction_type'],
    //             'transaction_amount' => $validatedData['transaction_amount'],
    //             'description' => $validatedData['description'] ?? null
    //         ]);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Transaction created successfully',
    //             'data' => $transaction
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'An error occurred. Please try again.'
    //         ], status: 500);
    //     }
    // }

    public function createTransaction(Request $request)
{
    $validatedData = $request->validate([
        'user_id' => 'required|exists:users,id',
        //'account_fund_id' => 'required|exists:account_funds,id',
        'transaction_date' => 'required|date',
        'title' => 'required|string|max:100',
        'transaction_type' => 'required|in:income,expense',
        'transaction_amount' => 'required|numeric|min:0',
        'description' => 'string|max:255'
    ]);

    try {
        // Get the last balance (if any) from the previous transaction
        $lastTransaction = OrgAccount::where('user_id', $validatedData['user_id'])
                                      ->latest('transaction_date')
                                      ->first();

        $previousBalance = $lastTransaction ? $lastTransaction->balance : 0;

        // Calculate new balance based on transaction type
        $newBalance = ($validatedData['transaction_type'] === 'income') 
                        ? $previousBalance + $validatedData['transaction_amount']
                        : $previousBalance - $validatedData['transaction_amount'];

        // Create the new transaction
        $transaction = OrgAccount::create([
            'user_id' => $validatedData['user_id'],
            //'account_fund_id' => $validatedData['account_fund_id'],
            'transaction_date' => $validatedData['transaction_date'],
            'title' => $validatedData['title'],
            'transaction_type' => $validatedData['transaction_type'],
            'transaction_amount' => $validatedData['transaction_amount'],
            'balance' => $newBalance, // Store the calculated balance
            'description' => $validatedData['description']
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Transaction created successfully',
            'data' => $transaction
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'An error occurred while creating the transaction. Please try again.',
            'error' => $e->getMessage()
        ], 500);
    }
}


    // Update an existing transaction
    // public function updateTransaction(Request $request, $id)
    // {
    //     $validatedData = $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //         'account_fund_id' => 'required|exists:account_funds,id',
    //         //'transaction_id' => 'required|numeric',
    //         'transaction_date' => 'required|date',
    //         'transaction_type' => 'required|in:income,expense',
    //         'transaction_amount' => 'required|numeric|min:0',
    //         'description' => 'required|string'
    //     ]);

    //     try {
    //         $transaction = OrgAccount::where('id', $id)->first();

    //         if (!$transaction) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Transaction not found.'
    //             ], 404);
    //         }

    //         $transaction->update($validatedData);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Transaction updated successfully',
    //             'data' => $transaction
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'An error occurred. Please try again.'
    //         ], 500);
    //     }
    // }

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