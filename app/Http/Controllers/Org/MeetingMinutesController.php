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
            'prepared_by' => 'required',
            'reviewed_by' => 'required',
            'privacy_setup_id' => 'required',
            'is_active' => 'required',
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
            Log::info('Meeting Minutes data: ', context: ['meeting_id' => $request->meeting_id, 'prepared_by' => $request->prepared_by, 'reviewed_by' => $request->reviewed_by, 'privacy_setup_id' => $request->privacy_setup_id]);

            // Create the Meeting Attendance record
            $meetingMinutes = MeetingMinutes::create([
                'meeting_id' => $request->meeting_id,
                'prepared_by' => $request->prepared_by,
                'reviewed_by' => $request->reviewed_by,
                'minutes' => $request->minutes,
                'decisions' => $request->decisions,
                'note' => $request->note,
                'file_attachments' => $request->file_attachments,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'follow_up_tasks' => $request->follow_up_tasks,
                'tags' => $request->tags,
                'action_items' => $request->action_items,
                'meeting_location' => $request->meeting_location,
                'video_link' => $request->video_link,
                'privacy_setup_id' => $request->privacy_setup_id,
                'approval_status' => $request->approval_status,
                'is_publish' => $request->is_publish,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $meetingMinutes, 'message' => 'Meeting Minutes created successfully.'], 201);
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
            'prepared_by' => 'required',
            'reviewed_by' => 'required',
            'privacy_setup_id' => 'required',
            'is_active' => 'required',
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
            'prepared_by' => $request->prepared_by,
            'reviewed_by' => $request->reviewed_by,
            'minutes' => $request->minutes,
            'decisions' => $request->decisions,
            'note' => $request->note,
            'file_attachments' => $request->file_attachments,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'follow_up_tasks' => $request->follow_up_tasks,
            'tags' => $request->tags,
            'action_items' => $request->action_items,
            'meeting_location' => $request->meeting_location,
            'video_link' => $request->video_link,
            'privacy_setup_id' => $request->privacy_setup_id,
            'approval_status' => $request->approval_status,
            'is_publish' => $request->is_publish,
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
