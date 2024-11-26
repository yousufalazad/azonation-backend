<?php

namespace App\Http\Controllers;

use App\Models\CountryRegion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
class CountryRegionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usersCountry = CountryRegion::select('country_regions.*', 'regions.name as region_name', 'countries.name as country_name')
            ->leftJoin('regions', 'country_regions.region_id', '=', 'regions.id')
            ->leftJoin('countries', 'country_regions.country_id', '=', 'countries.id')
            ->get();
        return response()->json(['status' => true, 'data' => $usersCountry], 200);
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
            'country_id' => 'required',
            'region_id' => 'required',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Logging the inputs for debugging
            Log::info('User Country data: ', ['country_id' => $request->country_id, 'region_id' => $request->region_id]);

            // Create the User Country record
            $dialingCode = CountryRegion::create([
                'country_id' => $request->country_id,
                'region_id' => $request->region_id,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $dialingCode, 'message' => 'User Country created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating Country: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create User Country.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CountryRegion $countryRegion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CountryRegion $countryRegion)
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
            'country_id' => 'required',
            'region_id' => 'required',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        // Find the dialingCode
        $dialingCode = CountryRegion::find($id);
        if (!$dialingCode) {
            return response()->json(['status' => false, 'message' => 'User Country not found.'], 404);
        }

        // Update the dialingCode
        $dialingCode->update([
            'country_id' => $request->country_id,
            'region_id' => $request->region_id,
            'is_active' => $request->is_active,
        ]);

        return response()->json(['status' => true, 'data' => $dialingCode, 'message' => 'User Country updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $dialingCode = CountryRegion::find($id);
        if (!$dialingCode) {
            return response()->json(['status' => false, 'message' => 'User Country not found.'], 404);
        }

        $dialingCode->delete();
        return response()->json(['status' => true, 'message' => 'User Country deleted successfully.'], 200);
    }
}
