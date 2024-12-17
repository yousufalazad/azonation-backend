<?php

namespace App\Http\Controllers;

use App\Models\RegionalTaxRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
class RegionalTaxRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $regionalTaxRate = RegionalTaxRate::select('regional_tax_rates.*', 'regions.name as region_name')
            ->leftJoin('regions', 'regional_tax_rates.region_id', '=', 'regions.id')
            ->get();
        return response()->json(['status' => true, 'data' =>$regionalTaxRate], 200);
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
            'tax_rate' => 'required',
            'region_id' => 'required',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Logging the inputs for debugging
            Log::info('Region Currency data: ', ['tax_rate' => $request->tax_rate , 'region_id' => $request->region_id]);

            // Create the Region Currency record
           $regionalTaxRate = RegionalTaxRate::create([
                'tax_rate' => $request->tax_rate ,
                'region_id' => $request->region_id,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' =>$regionalTaxRate, 'message' => 'Region Currency created successfully.'], 201);
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
    public function show(RegionalTaxRate $regionalTaxRate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RegionalTaxRate $regionalTaxRate)
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
            'tax_rate' => 'required',
            'region_id' => 'required',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        // Find the 
       $regionalTaxRate = RegionalTaxRate::find($id);
        if (!$regionalTaxRate) {
            return response()->json(['status' => false, 'message' => 'Region Currency not found.'], 404);
        }

        // Update the 
       $regionalTaxRate->update([
            'tax_rate' => $request->tax_rate ,
            'region_id' => $request->region_id,
            'is_active' => $request->is_active,
        ]);

        return response()->json(['status' => true, 'data' =>$regionalTaxRate, 'message' => 'Region Currency updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
       $regionalTaxRate = RegionalTaxRate::find($id);
        if (!$regionalTaxRate) {
            return response()->json(['status' => false, 'message' => 'Region Currency not found.'], 404);
        }

       $regionalTaxRate->delete();
        return response()->json(['status' => true, 'message' => 'Region Currency deleted successfully.'], 200);
    }
}
