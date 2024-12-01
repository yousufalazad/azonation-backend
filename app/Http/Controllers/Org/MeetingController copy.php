<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MeetingController extends Controller
{
    // Fetch meetings for the authenticated user
    public function getOrgMeeting(Request $request)
    {
        $user_id = $request->user()->id; // Retrieve the authenticated user's ID

        $meetings = Meeting::select('meetings.*', 'conduct_types.name as conduct_type_name')
            ->leftJoin('conduct_types', 'meetings.conduct_type_id', '=', 'conduct_types.id')
            ->where('meetings.user_id', $user_id)
            ->get();

        return response()->json(['status' => true, 'data' => $meetings]);
    }

    // Create a new meeting
    public function store(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'name' => 'required|string|max:255',
        //     'short_name' => 'nullable|string|max:100',
        //     'subject' => 'nullable|string|max:255',
        //     'date' => 'nullable|date',
        //     'start_time' => 'nullable|date_format:H:i',
        //     'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
        //     'meeting_type' => 'nullable|string|max:50',
        //     'timezone' => 'nullable|string|max:50',
        //     'meeting_mode' => 'nullable|string|in:in-person,remote,hybrid',
        //     'duration' => 'nullable|integer|min:0',
        //     'priority' => 'nullable|string|in:low,medium,high,urgent',
        //     'video_conference_link' => 'nullable|url',
        //     'access_code' => 'nullable|string|max:50',
        //     'recording_link' => 'nullable|url',
        //     'meeting_host' => 'nullable|string|max:255',
        //     'max_participants' => 'nullable|integer|min:1',
        //     'rsvp_status' => 'nullable|array',
        //     'rsvp_status.*' => 'string',
        //     'participants' => 'nullable|array',
        //     'participants.*' => 'string|max:255',
        //     'description' => 'nullable|string',
        //     'address' => 'nullable|string',
        //     'agenda' => 'nullable|string',
        //     'requirements' => 'nullable|string',
        //     'note' => 'nullable|string',
        //     'tags' => 'nullable|array',
        //     'tags.*' => 'string|max:50',
        //     'reminder_time' => 'nullable|integer|min:0',
        //     'repeat_frequency' => 'nullable|string|in:daily,weekly,monthly,yearly',
        //     'attachment' => 'nullable|string|max:255',
        //     'conduct_type_id' => 'nullable|exists:conduct_types,id',
        //     'is_active' => 'nullable|boolean',
        //     'privacy_setup_id' => 'nullable',
        //     'visibility' => 'nullable|string|in:private,public,members-only',
        //     'cancellation_reason' => 'nullable|string|max:255',
        //     'feedback_link' => 'nullable|url',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        // }

        $input = $request->all();
        $input['user_id'] = $request->user()->id;
        $input['created_by'] = $request->user()->id;
        // $input['updated_by'] = $request->user()->id;

        $meeting = Meeting::create($input);

        return response()->json(['status' => true, 'message' => 'Meeting created successfully', 'data' => $meeting], 201);
    }

    // Get a specific meeting
    public function show($id)
    {
        $meeting = Meeting::select('meetings.*', 'conduct_types.name as conduct_type_name')
            ->leftJoin('conduct_types', 'meetings.conduct_type_id', '=', 'conduct_types.id')
            ->where('meetings.id', $id)
            ->first();

        if (!$meeting) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $meeting], 200);
    }

    // Update a meeting
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'subject' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'meeting_type' => 'nullable|string|max:50',
            'timezone' => 'nullable|string|max:50',
            'meeting_mode' => 'nullable|string|in:in-person,remote,hybrid',
            'duration' => 'nullable|integer|min:0',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'video_conference_link' => 'nullable|url',
            'access_code' => 'nullable|string|max:50',
            'recording_link' => 'nullable|url',
            'meeting_host' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'rsvp_status' => 'nullable|array',
            'rsvp_status.*' => 'string',
            'participants' => 'nullable|array',
            'participants.*' => 'string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'agenda' => 'nullable|string',
            'requirements' => 'nullable|string',
            'note' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'reminder_time' => 'nullable|integer|min:0',
            'repeat_frequency' => 'nullable|string|in:daily,weekly,monthly,yearly',
            'attachment' => 'nullable|string|max:255',
            'conduct_type_id' => 'nullable|exists:conduct_types,id',
            'is_active' => 'nullable|boolean',
            'visibility' => 'nullable|string|in:private,public,members-only',
            'cancellation_reason' => 'nullable|string|max:255',
            'feedback_link' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }

        $meeting = Meeting::find($id);

        if (!$meeting) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }

        $meeting->update($request->all());

        return response()->json(['status' => true, 'message' => 'Meeting updated successfully', 'data' => $meeting]);
    }

    // Delete a meeting
    public function destroy($id)
    {
        $meeting = Meeting::find($id);

        if (!$meeting) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }

        $meeting->delete();

        return response()->json(['status' => true, 'message' => 'Meeting deleted successfully']);
    }
}
