<?php

namespace App\Http\Controllers;

use App\Models\PriceRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


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
   
        public function update(Request $request)
    {
        // Validate the input data
        $validator = Validator::make($request->all(), [
            'priceRates' => 'required|array',
            'priceRates.*.id' => 'required|exists:price_rates,id',
            'priceRates.*.region1' => 'nullable|numeric|min:0',
            'priceRates.*.region2' => 'nullable|numeric|min:0',
            'priceRates.*.region3' => 'nullable|numeric|min:0',
            // Add validation rules for all regions you have
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($request->priceRates as $rateData) {
                $priceRate = PriceRate::findOrFail($rateData['id']);
                
                // Update each region price field from the request
                foreach ($rateData as $regionKey => $value) {
                    if (str_starts_with($regionKey, 'region')) {
                        $priceRate->{$regionKey} = $value;
                    }
                }

                $priceRate->save();
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Price rates updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to update price rates',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PriceRate $priceRate)
    {
        //
    }
}
