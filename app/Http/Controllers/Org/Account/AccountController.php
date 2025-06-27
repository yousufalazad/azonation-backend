<?php
namespace App\Http\Controllers\Org\Account;
use App\Http\Controllers\Controller;

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
            Log::error('Error fetching packages: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
    public function index()
    {
        try {
            $userId = Auth::id();
            $transactions = Account::orderBy('date', 'desc')
                ->with(['funds','images', 'documents'])
                ->where('user_id', $userId)
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
            'fund_id' => 'required|exists:account_funds,id',
            'date' => 'required|date',
            'transaction_title' => 'required|string|max:100',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'description' => 'string|max:255'
        ]);
        try {
            $transaction = Account::create([
                'user_id' => $userId,
                'fund_id' => $validatedData['fund_id'],
                'date' => $validatedData['date'],
                'transaction_title' => $validatedData['transaction_title'],
                'type' => $validatedData['type'],
                'amount' => $validatedData['amount'],
                'description' => $validatedData['description']
            ]);
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/account/file',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    AccountTransactionFile::create([
                        'account_id' => $transaction->id,
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
                        'org/account/image',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    AccountTransactionImage::create([
                        'account_id' => $transaction->id,
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
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/account/doc',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    AccountTransactionFile::create([
                        'account_id' => $transaction->id,
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
                        'org/account/image',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    AccountTransactionImage::create([
                        'account_id' => $transaction->id,
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
