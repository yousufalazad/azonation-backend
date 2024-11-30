<?php

namespace App\Http\Controllers;

use App\Models\PrivacySetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PrivacySetupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $privacySetups = PrivacySetup::where('is_active', 1)
            ->orderBy('id', 'asc')
            ->get();
        return response()->json([
            'status' => true,
            'data' => $privacySetups
        ]);
    }

    public function getAllPrivacySetupForSuperAdmin()
    {
        $privacySetups = PrivacySetup::orderBy('id', 'asc')
            ->get();
        return response()->json([
            'status' => true,
            'data' => $privacySetups
        ]);
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
            'description' => 'required|string',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Logging the inputs for debugging
            Log::info('Privacy Setup data: ', ['name' => $request->name, 'description' => $request->description]);

            // Create the PrivacySetup record
            $privacySetup = PrivacySetup::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $privacySetup, 'message' => 'Privacy Setup created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating Privacy Setup: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create Privacy Setup.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PrivacySetup $privacySetup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PrivacySetup $privacySetup)
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
            'description' => 'required|string',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        // Find the country
        $privacySetup = PrivacySetup::find($id);
        if (!$privacySetup) {
            return response()->json(['status' => false, 'message' => 'Privacy Setup not found.'], 404);
        }

        // Update the country
        $privacySetup->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->is_active,
        ]);

        return response()->json(['status' => true, 'data' => $privacySetup, 'message' => 'Privacy Setup updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $privacySetup = PrivacySetup::find($id);
        if (!$privacySetup) {
            return response()->json(['status' => false, 'message' => 'Privacy Setup not found.'], 404);
        }

        $privacySetup->delete();
        return response()->json(['status' => true, 'message' => 'Privacy Setup deleted successfully.'], 200);
    }
}
