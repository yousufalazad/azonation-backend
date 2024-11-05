<?php

namespace App\Http\Controllers;

use App\Models\PriceRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class PriceRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            //$user_id = $request->user()->id; // Retrieve the authenticated user's ID
            $priceRates = PriceRate::all();
            // Return JSON response with status and data
            return response()->json([
                'status' => true,
                'data' => $priceRates,
            ]);
        } catch (\Exception $e) {
            // Log the exception for debugging
            Log::error('Error fetching PriceRate: ' . $e->getMessage());

            // Return JSON response with error status
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching PriceRate.',
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PriceRate $priceRate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PriceRate $priceRate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PriceRate $priceRate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PriceRate $priceRate)
    {
        //
    }
}
