<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Get the authenticated user
         $user_id = $request->user()->id;

         // Fetch invoices related to the authenticated user
         $invoices = Invoice::where('user_id', $user_id)->get();
 
         // Return the invoices data as a JSON response
         return response()->json([
             'status' => true,
             'data' => $invoices,
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

    // public function indexForSuperAdmin(Request $request)
    // {
    //      // Get the authenticated user
    //      $user_id = $request->user()->id;

    //     // Fetch invoices related to the authenticated user
    //     $invoices = Invoice::where('user_id', $user_id)->get();

    //     // Return the invoices data as a JSON response
    //     return response()->json([
    //         'status' => true,
    //         'data' => $invoices,
    //     ]);
    // }

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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }
}
