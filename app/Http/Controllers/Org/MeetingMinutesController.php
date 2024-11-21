<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\MeetingMinutes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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
            'meeting_id' => 'required|integer|exists:meetings,id',
            'prepared_by' => 'required|integer|exists:users,id',
            'reviewed_by' => 'required|integer|exists:users,id',
            'privacy_setup_id' => 'required|integer|exists:privacy_setups,id',
            'is_active' => 'required|boolean',
            'file_attachments' => 'nullable|file|mimes:pdf,doc,docx|max:1024', // Validate document file
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Handle the file upload
            $fileAttachmentPath = null;
            if ($request->hasFile('file_attachments')) {
                $file = $request->file('file_attachments');
                $fileAttachmentPath = $file->storeAs(
                    'org/docs',
                    now()->format('YmdHis') . '_' . $file->getClientOriginalName(),
                    'public'
                );
            }

            // Create a new MeetingMinutes record
            $meetingMinutes = new MeetingMinutes();
            $meetingMinutes->meeting_id = $request->meeting_id;
            $meetingMinutes->prepared_by = $request->prepared_by;
            $meetingMinutes->reviewed_by = $request->reviewed_by;
            $meetingMinutes->minutes = $request->minutes;
            $meetingMinutes->decisions = $request->decisions;
            $meetingMinutes->note = $request->note;
            $meetingMinutes->file_attachments = $fileAttachmentPath; // Save file path
            $meetingMinutes->start_time = $request->start_time;
            $meetingMinutes->end_time = $request->end_time;
            $meetingMinutes->follow_up_tasks = $request->follow_up_tasks;
            $meetingMinutes->tags = $request->tags;
            $meetingMinutes->action_items = $request->action_items;
            $meetingMinutes->meeting_location = $request->meeting_location;
            $meetingMinutes->video_link = $request->video_link;
            $meetingMinutes->privacy_setup_id = $request->privacy_setup_id;
            $meetingMinutes->approval_status = $request->approval_status;
            $meetingMinutes->is_publish = $request->is_publish;
            $meetingMinutes->is_active = $request->is_active;

            // Save the record to the database
            $meetingMinutes->save();

            // Return success response
            return response()->json([
                'status' => true,
                'data' => $meetingMinutes,
                'message' => 'Meeting Minutes created successfully.'
            ], 201);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error creating Meeting Minutes: ' . $e->getMessage());

            // Return generic error response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $meetingMinute =  MeetingMinutes::select('meeting_minutes.*', 'privacy_setups.id as privacy_id', 'privacy_setups.name as privacy_setup_name')
            ->leftJoin('privacy_setups', 'meeting_minutes.privacy_setup_id', '=', 'privacy_setups.id')
            ->where('meeting_minutes.id', $id)->first();

        // Check if meeting exists
        if (!$meetingMinute) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }

        // Return the meeting data
        return response()->json(['status' => true, 'data' => $meetingMinute], 200);
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
            'meeting_id' => 'required|integer|exists:meetings,id',
            'prepared_by' => 'required|integer|exists:users,id',
            'reviewed_by' => 'required|integer|exists:users,id',
            'privacy_setup_id' => 'required|integer|exists:privacy_setups,id',
            'is_active' => 'required|boolean',
            'file_attachments' => 'nullable|file|mimes:pdf,doc,docx|max:1024', // Validate document file
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find the record by ID
            $meetingMinutes = MeetingMinutes::findOrFail($id);

            // Handle the file upload
            if ($request->hasFile('file_attachments')) {
                //Delete the old file if exists
                if ($meetingMinutes->file_attachments && Storage::exists('public/' . $meetingMinutes->file_attachments)) {
                    Storage::delete('public/' . $meetingMinutes->file_attachments);
                }

                $file = $request->file('file_attachments');
                $fileAttachmentPath = $file->storeAs(
                    'org/docs',
                    now()->format('YmdHis') . '_' . $file->getClientOriginalName(),
                    'public'
                );

                $meetingMinutes->file_attachments = $fileAttachmentPath; // Update file path
            }

            // Update the MeetingMinutes record
            $meetingMinutes->meeting_id = $request->meeting_id;
            $meetingMinutes->prepared_by = $request->prepared_by;
            $meetingMinutes->reviewed_by = $request->reviewed_by;
            $meetingMinutes->minutes = $request->minutes;
            $meetingMinutes->decisions = $request->decisions;
            $meetingMinutes->note = $request->note;
            $meetingMinutes->start_time = $request->start_time;
            $meetingMinutes->end_time = $request->end_time;
            $meetingMinutes->follow_up_tasks = $request->follow_up_tasks;
            $meetingMinutes->tags = $request->tags;
            $meetingMinutes->action_items = $request->action_items;
            $meetingMinutes->meeting_location = $request->meeting_location;
            $meetingMinutes->video_link = $request->video_link;
            $meetingMinutes->privacy_setup_id = $request->privacy_setup_id;
            $meetingMinutes->approval_status = $request->approval_status;
            $meetingMinutes->is_publish = $request->is_publish;
            $meetingMinutes->is_active = $request->is_active;

            // Save the updated record
            $meetingMinutes->save();

            // Return success response
            return response()->json([
                'status' => true,
                'data' => $meetingMinutes,
                'message' => 'Meeting Minutes updated successfully.'
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error updating Meeting Minutes: ' . $e->getMessage());

            // Return generic error response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
{
    try {
        // Find the record by ID
        $meetingMinutes = MeetingMinutes::findOrFail($id);

        // Delete the file attachment if it exists
        if ($meetingMinutes->file_attachments && Storage::exists('public/' . $meetingMinutes->file_attachments)) {
            Storage::delete('public/' . $meetingMinutes->file_attachments);
        }

        // Delete the record from the database
        $meetingMinutes->delete();

        // Return success response
        return response()->json([
            'status' => true,
            'message' => 'Meeting Minutes deleted successfully.'
        ], 200);
    } catch (\Exception $e) {
        // Log the error for debugging
        Log::error('Error deleting Meeting Minutes: ' . $e->getMessage());

        // Return generic error response
        return response()->json([
            'status' => false,
            'message' => 'An error occurred. Please try again.'
        ], 500);
    }
}

}
