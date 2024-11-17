<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MeetingAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MeetingAttendanceController extends Controller
{
    public function getOrgUse()
    {
        $users = User::where('type', 'individual')->get();
        return response()->json(['status' => true, 'data' => $users], 200);
    }
    public function index()
    {
        $meetingAttendance = MeetingAttendance::select('meeting_attendances.*', 'users.name as user_name', 'meeting_attendance_types.name as attendance_types_name')
            ->leftJoin('users', 'meeting_attendances.user_id', '=', 'users.id')
            ->leftJoin('meeting_attendance_types', 'meeting_attendances.attendance_type', '=', 'meeting_attendance_types.id')
            ->get();
        return response()->json(['status' => true, 'data' => $meetingAttendance], 200);
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
            'meeting_id' => 'required',
            'user_id' => 'required',
            'attendance_type' => 'required',
            'date' => 'nullable',
            'time' => 'nullable',
            'note' => 'nullable',
            'is_active' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Logging the inputs for debugging
            Log::info('Meeting Attendance data: ', ['attendance_type' => $request->attendance_type, 'user_id' => $request->user_id]);

            // Create the Meeting Attendance record
            $meetingAttendances = MeetingAttendance::create([
                'meeting_id' => $request->meeting_id,
                'user_id' => $request->user_id,
                'attendance_type' => $request->attendance_type,
                'date' => $request->date,
                'time' => $request->time,
                'note' => $request->note,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $meetingAttendances, 'message' => 'Meeting Attendance created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating Country: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create Meeting Attendance.'], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(MeetingAttendance $meetingAttendance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MeetingAttendance $meetingAttendance)
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
            'meeting_id' => 'required',
            'user_id' => 'required',
            'attendance_type' => 'required',
            'date' => 'nullable',
            'time' => 'nullable',
            'note' => 'nullable',
            'is_active' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        // Find the Meeting Attendances
        $meetingAttendances = MeetingAttendance::find($id);
        if (!$meetingAttendances) {
            return response()->json(['status' => false, 'message' => 'Meeting Attendance not found.'], 404);
        }

        // Update the Meeting Attendances
        $meetingAttendances->update([
            'meeting_id' => $request->meeting_id,
            'user_id' => $request->user_id,
            'attendance_type' => $request->attendance_type,
            'date' => $request->date,
            'time' => $request->time,
            'note' => $request->note,
            'is_active' => $request->is_active,
        ]);

        return response()->json(['status' => true, 'data' => $meetingAttendances, 'message' => 'Meeting Attendance updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $meetingAttendance = MeetingAttendance::find($id);
        if (!$meetingAttendance) {
            return response()->json(['status' => false, 'message' => 'Meeting Attendance member not found.'], 404);
        }

        $meetingAttendance->delete();
        return response()->json(['status' => true, 'message' => 'Meeting Attendance deleted successfully.'], 200);
    }
}
