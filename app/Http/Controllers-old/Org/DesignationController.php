<?php

namespace App\Http\Controllers\Org;
use App\Http\Controllers\Controller;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
class DesignationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $designation = Designation::all();
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
            'name' => 'required|string|max:255',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Logging the inputs for debugging
            Log::info('Designation data: ', ['name' => $request->name, 'status' => $request->status]);

            // Create the Designation record
            $designation = Designation::create([
                'name' => $request->name,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $designation, 'message' => 'Designation created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating Designation: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create Designation.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Designation $designation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Designation $designation)
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
        // Find the designation
        $designation = Designation::find($id);
        if (!$designation) {
            return response()->json(['status' => false, 'message' => 'Designation not found.'], 404);
        }

        // Update the designation
        $designation->update([
            'name' => $request->name,
            'is_active' => $request->is_active,
        ]);

        return response()->json(['status' => true, 'data' => $designation, 'message' => 'Designation updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $designation = Designation::find($id);
        if (!$designation) {
            return response()->json(['status' => false, 'message' => 'Designation not found.'], 404);
        }

        $designation->delete();
        return response()->json(['status' => true, 'message' => 'Designation deleted successfully.'], 200);
    }
}
