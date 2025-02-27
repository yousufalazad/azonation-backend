<?php

namespace App\Http\Controllers\Org;
use App\Http\Controllers\Controller;
use App\Models\MeetingConductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
class MeetingConductTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $countries = MeetingConductType::all();
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

            // Create the MeetingConductType record
            $meetingConductType = MeetingConductType::create([
                'name' => $request->name,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $meetingConductType, 'message' => 'MeetingConductType created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating MeetingConductType: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create MeetingConductType.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MeetingConductType $meetingConductType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MeetingConductType $meetingConductType)
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
        $meetingConductType = MeetingConductType::find($id);
        if (!$meetingConductType) {
            return response()->json(['status' => false, 'message' => 'MeetingConductType not found.'], 404);
        }

        // Update the meetingConductType
        $meetingConductType->update([
            'name' => $request->name,
            'is_active' => $request->is_active,
        ]);

        return response()->json(['status' => true, 'data' => $meetingConductType, 'message' => 'MeetingConductType updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $meetingConductType = MeetingConductType::find($id);
        if (!$meetingConductType) {
            return response()->json(['status' => false, 'message' => 'MeetingConductType not found.'], 404);
        }

        $meetingConductType->delete();
        return response()->json(['status' => true, 'message' => 'MeetingConductType deleted successfully.'], 200);
    }
}
