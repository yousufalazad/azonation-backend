<?php
namespace App\Http\Controllers\Org\Meeting;

use App\Http\Concerns\ResolvesCurrentOrg;
use App\Models\AttendanceStatus;
use App\Models\Meeting;
// use App\Http\Controllers\Controller;
use Illuminate\Routing\Controller;

use App\Models\MeetingGuestAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MeetingGuestAttendanceController extends Controller
{
    use ResolvesCurrentOrg;

    public function __construct()
    {
        $this->middleware('org.permission:meeting-guest-attendance.read')->only(['index', 'show']);
        $this->middleware('org.permission:meeting-guest-attendance.create')->only(['create', 'store']);
        $this->middleware('org.permission:meeting-guest-attendance.update')->only(['edit', 'update']);
        $this->middleware('org.permission:meeting-guest-attendance.delete')->only(['destroy']);
    }
    public function index(Request $request)
    {
        $meetingAttendance = $this->ownedVia(MeetingGuestAttendance::class, 'meeting_id', Meeting::class)->select('meeting_guest_attendances.*', 'attendance_types.name as attendance_types_name', 'attendance_statuses.name as attendance_status_name', 'attendance_statuses.is_attended as is_attended')
            ->leftJoin('attendance_types', 'meeting_guest_attendances.attendance_type_id', '=', 'attendance_types.id')
            ->leftJoin('attendance_statuses', 'meeting_guest_attendances.attendance_status_id', '=', 'attendance_statuses.id')
            // ?meeting_id=5 returns one meeting's guests only
            ->when($request->query('meeting_id'), fn ($q, $meetingId) => $q->where('meeting_guest_attendances.meeting_id', $meetingId))
            ->get();
        return response()->json(['status' => true, 'data' => $meetingAttendance], 200);
    }
    public function create() {}
    public function store(Request $request)
    {
        // The meeting must belong to this organisation
        $this->ensureOwnedParent(Meeting::class, $request->input('meeting_id'));

        $validator = Validator::make($request->all(), [
            'meeting_id' => 'required',
            'guest_name' => 'required|string|max:255',
            'about_guest' => 'nullable',
            'attendance_status_id' => 'required|exists:attendance_statuses,id',
            'attendance_type_id' => 'nullable|exists:attendance_types,id',
            'date' => 'nullable',
            'time' => 'nullable',
            'note' => 'nullable',
            'is_active' => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        // Someone who attended needs a "how" (In Person, Online...); someone absent has none
        $didAttend = (bool) AttendanceStatus::whereKey($request->attendance_status_id)->value('is_attended');
        if ($didAttend && !$request->attendance_type_id) {
            return response()->json(['status' => false, 'message' => 'Choose how the guest attended (for example In Person or Online).'], 422);
        }
        try {
            $meetingAttendances = MeetingGuestAttendance::create([
                'meeting_id' => $request->meeting_id,
                'guest_name' => $request->guest_name,
                'about_guest' => $request->about_guest,
                'attendance_status_id' => $request->attendance_status_id,
                'attendance_type_id' => $didAttend ? $request->attendance_type_id : null,
                'date' => $request->date,
                'time' => $didAttend ? $request->time : null,
                'note' => $request->note,
                'is_active' => $request->has('is_active') && !$request->boolean('is_active') ? '0' : '1',
            ]);
            return response()->json(['status' => true, 'data' => $meetingAttendances, 'message' => 'Meeting Attendance created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating meeting guest: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create Meeting Attendance.'], 500);
        }
    }
    public function show(MeetingGuestAttendance $guestMeetingAttendance) {}
    public function edit(MeetingGuestAttendance $guestMeetingAttendance) {}
    public function update(Request $request, $id)
    {
        // The meeting must belong to this organisation
        if ($request->has('meeting_id')) $this->ensureOwnedParent(Meeting::class, $request->input('meeting_id'));

        $validator = Validator::make($request->all(), [
            'meeting_id' => 'required',
            'guest_name' => 'required|string|max:255',
            'about_guest' => 'nullable',
            'attendance_status_id' => 'required|exists:attendance_statuses,id',
            'attendance_type_id' => 'nullable|exists:attendance_types,id',
            'date' => 'nullable',
            'time' => 'nullable',
            'note' => 'nullable',
            'is_active' => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        // Someone who attended needs a "how" (In Person, Online...); someone absent has none
        $didAttend = (bool) AttendanceStatus::whereKey($request->attendance_status_id)->value('is_attended');
        if ($didAttend && !$request->attendance_type_id) {
            return response()->json(['status' => false, 'message' => 'Choose how the guest attended (for example In Person or Online).'], 422);
        }
        $meetingAttendances = $this->ownedVia(MeetingGuestAttendance::class, 'meeting_id', Meeting::class)->find($id);
        if (!$meetingAttendances) {
            return response()->json(['status' => false, 'message' => 'Meeting Attendance not found.'], 404);
        }
        $meetingAttendances->update([
            'meeting_id' => $request->meeting_id,
            'guest_name' => $request->guest_name,
            'about_guest' => $request->about_guest,
            'attendance_status_id' => $request->attendance_status_id,
            'attendance_type_id' => $didAttend ? $request->attendance_type_id : null,
            'date' => $request->date,
            'time' => $didAttend ? $request->time : null,
            'note' => $request->note,
            'is_active' => $request->has('is_active') && !$request->boolean('is_active') ? '0' : '1',
        ]);
        return response()->json(['status' => true, 'data' => $meetingAttendances, 'message' => 'Meeting Attendance updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $meetingAttendance = $this->ownedVia(MeetingGuestAttendance::class, 'meeting_id', Meeting::class)->find($id);
        if (!$meetingAttendance) {
            return response()->json(['status' => false, 'message' => 'Meeting Attendance member not found.'], 404);
        }
        $meetingAttendance->delete();
        return response()->json(['status' => true, 'message' => 'Meeting Attendance deleted successfully.'], 200);
    }
}
