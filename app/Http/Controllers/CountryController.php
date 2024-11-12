<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $countries = Country::all();
        return response()->json(['status' => true, 'data' => $countries], 200);
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
            'country_name' => 'required|string|max:255',
            'iso_code' => 'required|string|max:4',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Logging the inputs for debugging
            Log::info('Country data: ', ['country_name' => $request->country_name, 'iso_code' => $request->iso_code]);

            // Create the Country record
            $country = Country::create([
                'country_name' => $request->country_name,
                'iso_code' => $request->iso_code,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $country, 'message' => 'Country created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating Country: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create Country.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Country $country)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Country $country)
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
            'country_name' => 'required|string|max:255',
            'iso_code' => 'required|string|max:4',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        // Find the country
        $country = Country::find($id);
        if (!$country) {
            return response()->json(['status' => false, 'message' => 'Country not found.'], 404);
        }

        // Update the country
        $country->update([
            'country_name' => $request->country_name,
            'iso_code' => $request->iso_code,
            'is_active' => $request->is_active,
        ]);

        return response()->json(['status' => true, 'data' => $country, 'message' => 'Country updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $country = Country::find($id);
        if (!$country) {
            return response()->json(['status' => false, 'message' => 'Country not found.'], 404);
        }

        $country->delete();
        return response()->json(['status' => true, 'message' => 'Country deleted successfully.'], 200);
    }
}
