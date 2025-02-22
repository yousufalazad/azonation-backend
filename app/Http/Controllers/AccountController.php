<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransactionImage;
use App\Models\AccountTransactionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;


class AccountController extends Controller
{
    // Fetch all transactions for the authenticated user
    public function __getTransactions()
    {
        try {
            $transactions = Account::orderBy('date', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'data' => $transactions
            ], 200);
        } catch (\Exception $e) {
            // Log the exception for debugging
            Log::error('Error fetching packages: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    public function getTransactions()
    {
        try {
            $userId = Auth::id();
            // Fetch transactions with related images and documents
            $transactions = Account::orderBy('date', 'desc')
                ->with(['images', 'documents'])
                ->where('user_id', $userId)
                ->get();

            // Map each transaction to include full URLs for images and documents
            $transactions = $transactions->map(function ($transaction) {
                // Map over the images to include their full URLs
                $transaction->images = $transaction->images->map(function ($image) {
                    $image->image_url = $image->file_path
                        ? url(Storage::url($image->file_path))
                        : null;
                    return $image;
                });

                // Map over the documents to include their full URLs
                $transaction->documents = $transaction->documents->map(function ($document) {
                    $document->document_url = $document->file_path
                        ? url(Storage::url($document->file_path))
                        : null;
                    return $document;
                });

                return $transaction;
            });

            return response()->json([
                'status' => true,
                'data' => $transactions
            ], 200);
        } catch (\Exception $e) {
            // Log the exception for debugging
            Log::error('Error fetching transactions: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }


    public function createTransaction(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'fund_id' => 'required|exists:account_funds,id',
            'date' => 'required|date',
            'transaction_title' => 'required|string|max:100',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'description' => 'string|max:255'
        ]);

        try {
            // Create the new transaction
            $transaction = Account::create([
                'user_id' => $validatedData['user_id'],
                'fund_id' => $validatedData['fund_id'],
                'date' => $validatedData['date'],
                'transaction_title' => $validatedData['transaction_title'],
                'type' => $validatedData['type'],
                'amount' => $validatedData['amount'],
                'description' => $validatedData['description']
            ]);

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/doc/account',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );

                    AccountTransactionFile::create([
                        'account_id' => $transaction->id,
                        'file_path' => $documentPath, // Store the document path
                        'file_name' => $document->getClientOriginalName(), // Store the document name
                        'mime_type' => $document->getClientMimeType(), // Store the MIME type
                        'file_size' => $document->getSize(), // Store the size of the document
                        'is_public' => true, // Set the document as public
                        'is_active' => true, // Set the document as active
                    ]);
                }
            }

            // // Handle multiple image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->storeAs(
                        'org/image/account',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );

                    AccountTransactionImage::create([
                        'account_id' => $transaction->id,
                        'file_path' => $imagePath, // Store the document path
                        'file_name' => $image->getClientOriginalName(), // Store the document name
                        'mime_type' => $image->getClientMimeType(), // Store the MIME type
                        'file_size' => $image->getSize(), // Store the size of the document
                        'is_public' => true, // Set the document as public
                        'is_active' => true, // Set the document as active
                    ]);
                }
            }

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
    public function updateTransaction(Request $request, $id)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'fund_id' => 'required|exists:account_funds,id',
            'date' => 'required|date',
            'transaction_title' => 'required|string|max:100',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'description' => 'string|max:255'
        ]);

        try {
            $transaction = Account::where('id', $id)->first();

            if (!$transaction) {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaction not found.'
                ], 404);
            }

            $transaction->update($validatedData);

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/doc/account',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );

                    AccountTransactionFile::create([
                        'account_id' => $transaction->id,
                        'file_path' => $documentPath, // Store the document path
                        'file_name' => $document->getClientOriginalName(), // Store the document name
                        'mime_type' => $document->getClientMimeType(), // Store the MIME type
                        'file_size' => $document->getSize(), // Store the size of the document
                        'is_public' => true, // Set the document as public
                        'is_active' => true, // Set the document as active
                    ]);
                }
            }

            // // Handle multiple image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->storeAs(
                        'org/image/account',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );

                    AccountTransactionImage::create([
                        'account_id' => $transaction->id,
                        'file_path' => $imagePath, // Store the document path
                        'file_name' => $image->getClientOriginalName(), // Store the document name
                        'mime_type' => $image->getClientMimeType(), // Store the MIME type
                        'file_size' => $image->getSize(), // Store the size of the document
                        'is_public' => true, // Set the document as public
                        'is_active' => true, // Set the document as active
                    ]);
                }
            }

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
            $transaction = Account::where('id', $id)->first();
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
