<?php

namespace App\Http\Controllers\Org;
use App\Http\Controllers\Controller;
use App\Models\MembershipType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
class MembershipTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $countries = MembershipType::all();
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

            // Create the MembershipType record
            $membershipType = MembershipType::create([
                'name' => $request->name,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $membershipType, 'message' => 'MembershipType created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating MembershipType: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create MembershipType.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MembershipType $membershipType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MembershipType $membershipType)
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
        // Find the membershipType
        $membershipType = MembershipType::find($id);
        if (!$membershipType) {
            return response()->json(['status' => false, 'message' => 'MembershipType not found.'], 404);
        }

        // Update the membershipType
        $membershipType->update([
            'name' => $request->name,
            'is_active' => $request->is_active,
        ]);

        return response()->json(['status' => true, 'data' => $membershipType, 'message' => 'MembershipType updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $membershipType = MembershipType::find($id);
        if (!$membershipType) {
            return response()->json(['status' => false, 'message' => 'MembershipType not found.'], 404);
        }

        $membershipType->delete();
        return response()->json(['status' => true, 'message' => 'MembershipType deleted successfully.'], 200);
    }
}
