<?php
namespace App\Http\Controllers\Org\Meeting;
use App\Http\Controllers\Controller;
use App\Models\MeetingGuestAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MeetingGuestAttendanceController extends Controller
{
    public function index()
    {
        $meetingAttendance = MeetingGuestAttendance::select('meeting_guest_attendances.*', 'attendance_types.name as attendance_types_name')
            ->leftJoin('attendance_types', 'meeting_guest_attendances.attendance_type_id', '=', 'attendance_types.id')
            ->get();
        return response()->json(['status' => true, 'data' => $meetingAttendance], 200);
    }
    public function create() {}
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'meeting_id' => 'required',
            'guest_name' => 'required',
            'about_guest' => 'nullable',
            'attendance_type_id' => 'nullable',
            'date' => 'nullable',
            'time' => 'nullable',
            'note' => 'nullable',
            'is_active' => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            Log::info('Meeting Attendance data: ', ['attendance_type_id' => $request->attendance_type_id, 'user_id' => $request->user_id]);
            $meetingAttendances = MeetingGuestAttendance::create([
                'meeting_id' => $request->meeting_id,
                'guest_name' => $request->guest_name,
                'about_guest' => $request->about_guest,
                'attendance_type_id' => $request->attendance_type_id,
                'date' => $request->date,
                'time' => $request->time,
                'note' => $request->note,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $meetingAttendances, 'message' => 'Meeting Attendance created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Country: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create Meeting Attendance.'], 500);
        }
    }
    public function show(MeetingGuestAttendance $guestMeetingAttendance) {}
    public function edit(MeetingGuestAttendance $guestMeetingAttendance) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'meeting_id' => 'required',
            'guest_name' => 'required',
            'about_guest' => 'nullable',
            'attendance_type_id' => 'nullable',
            'date' => 'nullable',
            'time' => 'nullable',
            'note' => 'nullable',
            'is_active' => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $meetingAttendances = MeetingGuestAttendance::find($id);
        if (!$meetingAttendances) {
            return response()->json(['status' => false, 'message' => 'Meeting Attendance not found.'], 404);
        }
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
    public function destroy($id)
    {
        $meetingAttendance = MeetingGuestAttendance::find($id);
        if (!$meetingAttendance) {
            return response()->json(['status' => false, 'message' => 'Meeting Attendance member not found.'], 404);
        }
        $meetingAttendance->delete();
        return response()->json(['status' => true, 'message' => 'Meeting Attendance deleted successfully.'], 200);
    }
}
