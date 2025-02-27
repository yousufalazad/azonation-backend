<?php

namespace App\Http\Controllers;

use App\Models\RegionCurrency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RegionCurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $regionCurrency = RegionCurrency::select('region_currencies.*', 'regions.name as region_name', 'currencies.name as currency_name')
            ->leftJoin('regions', 'region_currencies.region_id', '=', 'regions.id')
            ->leftJoin('currencies', 'region_currencies.currency_id', '=', 'currencies.id')
            ->get();
        return response()->json(['status' => true, 'data' => $regionCurrency], 200);
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
        // Validation
        $validator = Validator::make($request->all(), [
            'currency_id' => 'required',
            'region_id' => 'required',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Logging the inputs for debugging
            Log::info('Region Currency data: ', ['currency_id' => $request->currency_id , 'region_id' => $request->region_id]);

            // Create the Region Currency record
            $regionCurrency = RegionCurrency::create([
                'currency_id' => $request->currency_id ,
                'region_id' => $request->region_id,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $regionCurrency, 'message' => 'Region Currency created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating Country: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create Region Currency.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RegionCurrency $regionCurrency)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RegionCurrency $regionCurrency)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'currency_id' => 'required',
            'region_id' => 'required',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        // Find the 
        $regionCurrency = RegionCurrency::find($id);
        if (!$regionCurrency) {
            return response()->json(['status' => false, 'message' => 'Region Currency not found.'], 404);
        }

        // Update the 
        $regionCurrency->update([
            'currency_id' => $request->currency_id ,
            'region_id' => $request->region_id,
            'is_active' => $request->is_active,
        ]);

        return response()->json(['status' => true, 'data' => $regionCurrency, 'message' => 'Region Currency updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $regionCurrency = RegionCurrency::find($id);
        if (!$regionCurrency) {
            return response()->json(['status' => false, 'message' => 'Region Currency not found.'], 404);
        }

        $regionCurrency->delete();
        return response()->json(['status' => true, 'message' => 'Region Currency deleted successfully.'], 200);
    }
}
