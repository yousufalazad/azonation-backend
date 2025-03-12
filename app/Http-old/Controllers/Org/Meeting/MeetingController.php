<?php
namespace App\Http\Controllers\Org\Meeting;
use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MeetingController extends Controller
{
    public function getOrgMeeting(Request $request)
    {
        $user_id = $request->user()->id;
        $meetings = Meeting::select('meetings.*', 'conduct_types.name as conduct_type_name')
            ->leftJoin('conduct_types', 'meetings.conduct_type_id', '=', 'conduct_types.id')
            ->where('meetings.user_id', $user_id)
            ->get();
        return response()->json(['status' => true, 'data' => $meetings]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string',
            'subject' => 'nullable|string',
            'date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'meeting_type' => 'nullable|string|max:50',
            'timezone' => 'nullable|string|max:50',
            'meeting_mode' => 'nullable|string',
            'duration' => 'nullable|integer',
            'priority' => 'nullable|string',
            'video_conference_link' => 'nullable|url',
            'access_code' => 'nullable|string|max:50',
            'recording_link' => 'nullable|url',
            'meeting_host' => 'nullable|string',
            'max_participants' => 'nullable|integer',
            'rsvp_status' => 'nullable',
            'rsvp_status.*' => 'string',
            'participants' => 'nullable',
            'participants.*' => 'string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'agenda' => 'nullable|string',
            'requirements' => 'nullable|string',
            'note' => 'nullable|string',
            'tags' => 'nullable',
            'tags.*' => 'string|max:50',
            'reminder_time' => 'nullable|integer',
            'repeat_frequency' => 'nullable|string',
            'attachment' => 'nullable|string|max:255',
            'conduct_type_id' => 'nullable|exists:conduct_types,id',
            'is_active' => 'nullable|boolean',
            'visibility' => 'nullable|string',
            'cancellation_reason' => 'nullable|string',
            'feedback_link' => 'nullable|url',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }
        $input = $request->all();
        $input['user_id'] = $request->user()->id;
        $input['created_by'] = $request->user()->id;
        $meeting = Meeting::create($input);
        return response()->json(['status' => true, 'message' => 'Meeting created successfully', 'data' => $meeting], 201);
    }
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
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string',
            'subject' => 'nullable|string',
            'date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'meeting_type' => 'nullable|string|max:50',
            'timezone' => 'nullable|string|max:50',
            'meeting_mode' => 'nullable|string',
            'duration' => 'nullable|integer',
            'priority' => 'nullable|string',
            'video_conference_link' => 'nullable|url',
            'access_code' => 'nullable|string|max:50',
            'recording_link' => 'nullable|url',
            'meeting_host' => 'nullable|string',
            'max_participants' => 'nullable|integer',
            'rsvp_status' => 'nullable',
            'rsvp_status.*' => 'string',
            'participants' => 'nullable',
            'participants.*' => 'string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'agenda' => 'nullable|string',
            'requirements' => 'nullable|string',
            'note' => 'nullable|string',
            'tags' => 'nullable',
            'tags.*' => 'string|max:50',
            'reminder_time' => 'nullable|integer',
            'repeat_frequency' => 'nullable|string',
            'attachment' => 'nullable|string|max:255',
            'conduct_type_id' => 'nullable|exists:conduct_types,id',
            'is_active' => 'nullable|boolean',
            'visibility' => 'nullable|string',
            'cancellation_reason' => 'nullable|string',
            'feedback_link' => 'nullable|url',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }
        $meeting = Meeting::find($id);
        if (!$meeting) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }
        $input = $request->all();
        $input['updated_by'] = $request->user()->id;
        $meeting->update($input);
        return response()->json(['status' => true, 'message' => 'Meeting updated successfully', 'data' => $meeting]);
    }
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
