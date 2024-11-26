<?php

namespace App\Http\Controllers\Org;
use App\Http\Controllers\Controller;
use App\Models\GuestMeetingAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class GuestMeetingAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $meetingAttendance = GuestMeetingAttendance::select('guest_meeting_attendances.*', 'attendance_types.name as attendance_types_name')
            ->leftJoin('attendance_types', 'guest_meeting_attendances.attendance_type_id', '=', 'attendance_types.id')
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
            'guest_name' => 'required',
            'about_guest' => 'nullable',
            'attendance_type_id' => 'required',
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
            Log::info('Meeting Attendance data: ', ['attendance_type_id' => $request->attendance_type_id, 'user_id' => $request->user_id]);

            // Create the Meeting Attendance record
            $meetingAttendances = GuestMeetingAttendance::create([
                'meeting_id' => $request->meeting_id,
                'guest_name' => $request->guest_name,
                'about_guest' => $request->about_guest,
                'attendance_type_id' => $request->attendance_type_id,
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
    public function show(GuestMeetingAttendance $guestMeetingAttendance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GuestMeetingAttendance $guestMeetingAttendance)
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
            'guest_name' => 'required',
            'about_guest' => 'nullable',
            'attendance_type_id' => 'required',
            'date' => 'nullable',
            'time' => 'nullable',
            'note' => 'nullable',
            'is_active' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        // Find the Meeting Attendances
        $meetingAttendances = GuestMeetingAttendance::find($id);
        if (!$meetingAttendances) {
            return response()->json(['status' => false, 'message' => 'Meeting Attendance not found.'], 404);
        }

        // Update the Meeting Attendances
        $meetingAttendances->update([
            'meeting_id' => $request->meeting_id,
            'guest_name' => $request->guest_name,
            'about_guest' => $request->about_guest,
            'attendance_type_id' => $request->attendance_type_id,
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
        $meetingAttendance = GuestMeetingAttendance::find($id);
        if (!$meetingAttendance) {
            return response()->json(['status' => false, 'message' => 'Meeting Attendance member not found.'], 404);
        }

        $meetingAttendance->delete();
        return response()->json(['status' => true, 'message' => 'Meeting Attendance deleted successfully.'], 200);
    }
}
