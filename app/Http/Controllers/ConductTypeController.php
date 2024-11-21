<?php

namespace App\Http\Controllers;

use App\Models\ConductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
class ConductTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $countries = ConductType::all();
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
            'name' => 'required|string|max:255',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Logging the inputs for debugging
            Log::info('Meeting Conduct Type data: ', ['name' => $request->name, 'status' => $request->status]);

            // Create the ConductType record
            $conductType = ConductType::create([
                'name' => $request->name,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $conductType, 'message' => 'ConductType created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating ConductType: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create ConductType.'], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(ConductType $conductType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ConductType $conductType)
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
            'name' => 'required|string|max:255',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        // Find the meetingConductType
        $conductType = ConductType::find($id);
        if (!$conductType) {
            return response()->json(['status' => false, 'message' => 'ConductType not found.'], 404);
        }

        // Update the meetingConductType
        $conductType->update([
            'name' => $request->name,
            'is_active' => $request->is_active,
        ]);

        return response()->json(['status' => true, 'data' => $conductType, 'message' => 'ConductType updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $conductType = ConductType::find($id);
        if (!$conductType) {
            return response()->json(['status' => false, 'message' => 'ConductType not found.'], 404);
        }

        $conductType->delete();
        return response()->json(['status' => true, 'message' => 'ConductType deleted successfully.'], 200);
    }
}
