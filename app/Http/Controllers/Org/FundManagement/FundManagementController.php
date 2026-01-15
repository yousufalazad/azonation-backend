<?php

namespace App\Http\Controllers\Org\FundManagement;

use App\Http\Controllers\Controller;

use App\Models\FundManagement;
use App\Models\AccountsTransactionCurrency;
use App\Models\FundManagementImage;
use App\Models\FundManagementFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class FundManagementController extends Controller
{
    public function index()
    {
        try {
            $userId = Auth::id();
            $transactions = FundManagement::where('user_id', $userId)
                ->where('is_active', true)
                ->with(['funds:id,name', 'images', 'documents'])
                ->orderBy('date', 'desc')
                ->get();

            $transactions = $transactions->map(function ($transaction) {
                $transaction->images = $transaction->images->map(function ($image) {
                    $image->image_url = $image->file_path
                        ? url(Storage::url($image->file_path))
                        : null;
                    return $image;
                });
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
            Log::error('Error fetching transactions: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
    public function store(Request $request)
    {
        $userId = Auth::id();
        $validatedData = $request->validate([
            // 'user_id' => 'required|exists:users,id',
            'fund_id' => 'required|exists:funds,id',
            'date' => 'required|date',
            'transaction_title' => 'required|string|max:100',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean'
        ]);
        try {
            $transaction = FundManagement::create([
                'user_id' => $userId,
                'fund_id' => $validatedData['fund_id'],
                'date' => $validatedData['date'],
                'transaction_title' => $validatedData['transaction_title'],
                'type' => $validatedData['type'],
                'amount' => $validatedData['amount'],
                'description' => $validatedData['description'],
                'is_active' => $validatedData['is_active'] ?? true, // Default to true if not provided
            ])->fresh(); // Refresh the model to get the updated data

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/accounts/file',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    FundManagementFile::create([
                        'fund_management_id' => $transaction->id,
                        'file_path' => $documentPath,
                        'file_name' => $document->getClientOriginalName(),
                        'mime_type' => $document->getClientMimeType(),
                        'file_size' => $document->getSize(),
                        'is_public' => true,
                        'is_active' => true,
                    ]);
                }
            }
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->storeAs(
                        'org/accounts/image',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    FundManagementImage::create([
                        'fund_management_id' => $transaction->id,
                        'file_path' => $imagePath,
                        'file_name' => $image->getClientOriginalName(),
                        'mime_type' => $image->getClientMimeType(),
                        'file_size' => $image->getSize(),
                        'is_public' => true,
                        'is_active' => true,
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
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'fund_id' => 'required|exists:funds,id',
            'date' => 'required|date',
            'transaction_title' => 'required|string|max:100',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean'
        ]);
        try {
            $validatedData['user_id'] = Auth::id(); // Ensure user_id is set to the authenticated user
            $transaction = FundManagement::where('id', $id)->first();
            if (!$transaction) {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaction not found.'
                ], 404);
            }
            $transaction->update($validatedData);

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/accounts/doc',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    FundManagementFile::create([
                        'fund_management_id' => $transaction->id,
                        'file_path' => $documentPath,
                        'file_name' => $document->getClientOriginalName(),
                        'mime_type' => $document->getClientMimeType(),
                        'file_size' => $document->getSize(),
                        'is_public' => true,
                        'is_active' => true,
                    ]);
                }
            }
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->storeAs(
                        'org/accounts/image',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    FundManagementImage::create([
                        'fund_management_id' => $transaction->id,
                        'file_path' => $imagePath,
                        'file_name' => $image->getClientOriginalName(),
                        'mime_type' => $image->getClientMimeType(),
                        'file_size' => $image->getSize(),
                        'is_public' => true,
                        'is_active' => true,
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
    public function destroy($id)
    {
        try {
            $transaction = FundManagement::where('id', $id)->first();
            if (!$transaction) {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaction not found.'
                ], 404);
            }
            $transaction->delete();
            // Optionally delete associated files and images
            // FundManagementFile::where('accounts_id', $id)->delete();
            // FundManagementImage::where('accounts_id', $id)->delete();
            // Storage::deleteDirectory('public/org/accounts/file/' . $id);
            // Storage::deleteDirectory('public/org/accounts/image/' . $id);
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

    public function getTransactionCurrency(Request $request)
    {
        try {
            $user_id = Auth::id();
            $accountsTransactionCurrency = AccountsTransactionCurrency::where('user_id', $user_id)
                ->with(['currency'])
                ->first();

            return response()->json([
                'status' => true,
                'data' => $accountsTransactionCurrency
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching transaction currency: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching the transaction currency. Please try again.'
            ], 500);
        }
    }

    public function storeTransactionCurrency(Request $request)
    {
        try {
            $user_id = Auth::id();

            $validatedData = $request->validate([
                'currency_id' => 'required|exists:currencies,id',
                'is_active' => 'nullable|boolean'
            ]);

            // ✅ Optional: check if already exists to avoid duplicate insert
            $exists = AccountsTransactionCurrency::where('user_id', $user_id)->exists();

            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaction currency already exists for this user.'
                ], 409); // Conflict
            }

            $validatedData['user_id'] = $user_id;

            $accountsTransactionCurrency = AccountsTransactionCurrency::create($validatedData);

            return response()->json([
                'status' => true,
                'message' => 'Transaction currency created successfully',
                'data' => $accountsTransactionCurrency
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating transaction currency: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the transaction currency.',
                'error' => $e->getMessage() // optional for debugging
            ], 500);
        }
    }

    public function updateTransactionCurrency(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'currency_id' => 'required|exists:currencies,id',
                'is_active' => 'boolean'
            ]);
            $accountsTransactionCurrency = AccountsTransactionCurrency::where('id', $id)->first();
            if (!$accountsTransactionCurrency) {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaction currency not found.'
                ], 404);
            }
            $accountsTransactionCurrency->update($validatedData);
            return response()->json([
                'status' => true,
                'message' => 'Transaction currency updated successfully',
                'data' => $accountsTransactionCurrency
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating transaction currency: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the transaction currency. Please try again.'
            ], 500);
        }
    }
}