<?php

namespace App\Http\Controllers;

use App\Models\PaymentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     //
    // }
    public function index(Request $request)
    {
        try {
            //Get the authenticated user
            // $user_id = $request->user()->id;
            // $paymentLogs = PaymentLog::where('user_id', $user_id)->get();

            // Fetch invoices related to the authenticated user
            $paymentLogs = PaymentLog::get();

            // Return the invoices data as a JSON response
            return response()->json([
                'status' => true,
                'data' => $paymentLogs,
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
        // Validate incoming request data
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

        // Create a new transaction record
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

        // Save the transaction to the database
        $paymentLog->save();

        // Return a success response
        return response()->json([
            'status' => true,
            'message' => 'Transaction created successfully.',
            'transaction' => $paymentLog,
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show($paymentLogId)
    {
        // Find the Project by ID
        $paymentLog = PaymentLog::find($paymentLogId);

        // Check if Project exists
        if (!$paymentLog) {
            return response()->json(['status' => false, 'message' => 'Payment not found'], 404);
        }

        // Return the Project data
        return response()->json(['status' => true, 'data' => $paymentLog], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentLog $paymentLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validate incoming request data
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
            // Find the payment log by ID
            $paymentLog = PaymentLog::findOrFail($id);

            // Update the payment log with validated data
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

            // Save the updated payment log
            $paymentLog->save();

            // Return a success response
            return response()->json([
                'status' => true,
                'message' => 'Transaction updated successfully.',
                'transaction' => $paymentLog,
            ]);
        } catch (\Exception $e) {
            // Return an error response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    
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
