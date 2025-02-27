<?php

namespace App\Http\Controllers;

use App\Models\AttendanceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AttendanceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $attendanceTypes = AttendanceType::all();
        return response()->json(['status' => true, 'data' => $attendanceTypes], 200);
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

            // Create the AttendanceType record
            $attendanceType = AttendanceType::create([
                'name' => $request->name,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $attendanceType, 'message' => 'AttendanceType created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating AttendanceType: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create AttendanceType.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AttendanceType $attendanceType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AttendanceType $attendanceType)
    {
        //
    }

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
        $attendanceType = AttendanceType::find($id);
        if (!$attendanceType) {
            return response()->json(['status' => false, 'message' => 'AttendanceType not found.'], 404);
        }

        // Update the meetingAttendanceType
        $attendanceType->update([
            'name' => $request->name,
            'is_active' => $request->is_active,
        ]);

        return response()->json(['status' => true, 'data' => $attendanceType, 'message' => 'AttendanceType updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $attendanceType = AttendanceType::find($id);
        if (!$attendanceType) {
            return response()->json(['status' => false, 'message' => 'AttendanceType not found.'], 404);
        }

        $attendanceType->delete();
        return response()->json(['status' => true, 'message' => 'AttendanceType deleted successfully.'], 200);
    }
}
