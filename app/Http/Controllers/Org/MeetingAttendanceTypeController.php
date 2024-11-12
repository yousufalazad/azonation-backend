<?php

namespace App\Http\Controllers\Org;
use App\Http\Controllers\Controller;
use App\Models\MeetingAttendanceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
class MeetingAttendanceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $countries = MeetingAttendanceType::all();
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

            // Create the MeetingAttendanceType record
            $meetingAttendanceType = MeetingAttendanceType::create([
                'name' => $request->name,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $meetingAttendanceType, 'message' => 'MeetingAttendanceType created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating MeetingAttendanceType: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create MeetingAttendanceType.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MeetingAttendanceType $meetingAttendanceType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MeetingAttendanceType $meetingAttendanceType)
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
        // Find the meetingAttendanceType
        $meetingAttendanceType = MeetingAttendanceType::find($id);
        if (!$meetingAttendanceType) {
            return response()->json(['status' => false, 'message' => 'MeetingAttendanceType not found.'], 404);
        }

        // Update the meetingAttendanceType
        $meetingAttendanceType->update([
            'name' => $request->name,
            'is_active' => $request->is_active,
        ]);

        return response()->json(['status' => true, 'data' => $meetingAttendanceType, 'message' => 'MeetingAttendanceType updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $meetingAttendanceType = MeetingAttendanceType::find($id);
        if (!$meetingAttendanceType) {
            return response()->json(['status' => false, 'message' => 'MeetingAttendanceType not found.'], 404);
        }

        $meetingAttendanceType->delete();
        return response()->json(['status' => true, 'message' => 'MeetingAttendanceType deleted successfully.'], 200);
    }
}
