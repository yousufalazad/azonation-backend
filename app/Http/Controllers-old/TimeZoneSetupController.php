<?php

namespace App\Http\Controllers;

use App\Models\TimeZoneSetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
class TimeZoneSetupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $designation = TimeZoneSetup::all();
        return response()->json(['status' => true, 'data' => $designation], 200);
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
            'time_zone' => 'required|string|max:255',
            'offset' => 'required',
            'description' => 'required',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Logging the inputs for debugging
            Log::info('TimeZoneSetup data: ', ['time_zone' => $request->time_zone, 'offset' => $request->offset, 'description' => $request->description, 'is_active' => $request->is_active]);

            // Create the TimeZoneSetup record
            $designation = TimeZoneSetup::create([
                'time_zone' => $request->time_zone,
                'offset' => $request->offset,
                'description' => $request->description,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $designation, 'message' => 'TimeZoneSetup created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating TimeZoneSetup: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create TimeZoneSetup.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TimeZoneSetup $timeZoneSetup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TimeZoneSetup $timeZoneSetup)
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
            'time_zone' => 'required|string|max:255',
            'offset' => 'required',
            'description' => 'required',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        // Find the designation
        $designation = TimeZoneSetup::find($id);
        if (!$designation) {
            return response()->json(['status' => false, 'message' => 'TimeZoneSetup not found.'], 404);
        }

        // Update the designation
        $designation->update([
            'time_zone' => $request->time_zone,
            'offset' => $request->offset,
            'description' => $request->description,
            'is_active' => $request->is_active,
        ]);

        return response()->json(['status' => true, 'data' => $designation, 'message' => 'TimeZoneSetup updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $designation = TimeZoneSetup::find($id);
        if (!$designation) {
            return response()->json(['status' => false, 'message' => 'TimeZoneSetup not found.'], 404);
        }

        $designation->delete();
        return response()->json(['status' => true, 'message' => 'TimeZoneSetup deleted successfully.'], 200);
    }
}
