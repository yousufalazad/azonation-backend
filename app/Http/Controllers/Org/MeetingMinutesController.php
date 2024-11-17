<?php

namespace App\Http\Controllers\Org;
use App\Http\Controllers\Controller;
use App\Models\MeetingMinutes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MeetingMinutesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $meetingAttendance = MeetingMinutes::get();
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
            'minutes' => 'required',
            // 'decisions' => 'required',
            // 'note' => 'nullable',
            // 'start_time' => 'nullable',
            // 'end_time' => 'nullable',
            // 'follow_up_tasks' => 'nullable',
            // 'tags' => 'nullable',
            // 'action_items' => 'nullable',
            // 'file_attachments' => 'nullable',
            // 'video_link' => 'nullable',
            // 'meeting_location' => 'nullable',
            // 'confidentiality' => 'nullable',
            // 'approval_status' => 'nullable',
            // 'status' => 'nullable',
            // 'prepared_by' => 'nullable',
            // 'reviewed_by' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Logging the inputs for debugging
            Log::info('Meeting Minutes data: ', context: ['meeting_id' => $request->meeting_id, 'minutes' => $request->minutes]);

            // Create the Meeting Attendance record
            $meetingAttendances = MeetingMinutes::create([
                'meeting_id' => $request->meeting_id,
                'minutes' => $request->minutes,
                'decisions' => $request->decisions,
                'note' => $request->note,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'follow_up_tasks' => $request->follow_up_tasks,
                'tags' => $request->tags,
                'action_items' => $request->action_items,
                'file_attachments' => $request->file_attachments,
                'video_link' => $request->video_link,
                'meeting_location' => $request->meeting_location,
                'confidentiality' => $request->confidentiality,
                'approval_status' => $request->approval_status,
                'status' => $request->status,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $meetingAttendances, 'message' => 'Meeting Attendance created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating Country: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create Meeting Attendance.'], status: 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MeetingMinutes $meetingMinutes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MeetingMinutes $meetingMinutes)
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
        $meetingAttendances = MeetingMinutes::find($id);
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
        $meetingAttendance = MeetingMinutes::find($id);
        if (!$meetingAttendance) {
            return response()->json(['status' => false, 'message' => 'Meeting Attendance member not found.'], 404);
        }

        $meetingAttendance->delete();
        return response()->json(['status' => true, 'message' => 'Meeting Attendance deleted successfully.'], 200);
    }
}
