<?php
namespace App\Http\Controllers\Org\Meeting;
// use App\Http\Controllers\Controller;
use Illuminate\Routing\Controller;

use App\Models\MeetingAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MeetingAttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('org.permission:meeting-attendance.read')->only(['index', 'show']);
        $this->middleware('org.permission:meeting-attendance.create')->only(['create', 'store', 'bulkStore']);
        $this->middleware('org.permission:meeting-attendance.update')->only(['edit', 'update']);
        $this->middleware('org.permission:meeting-attendance.delete')->only(['destroy']);
    }
    public function index()
    {
        $meetingAttendance = MeetingAttendance::select('meeting_attendances.*', 'users.first_name as user_first_name', 'users.last_name as user_last_name', 'attendance_types.name as attendance_types_name')
            ->leftJoin('users', 'meeting_attendances.user_id', '=', 'users.id')
            ->leftJoin('attendance_types', 'meeting_attendances.attendance_type_id', '=', 'attendance_types.id')
            ->get();
        return response()->json(['status' => true, 'data' => $meetingAttendance], 200);
    }
    public function create() {}
    /**
     * Bulk insert or update meeting attendance
     */
    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '*.meeting_id'           => 'required|exists:meetings,id',
            '*.user_id'              => 'required|exists:users,id',
            '*.attendance_type_id'   => 'required|exists:attendance_types,id',
            '*.time'                 => 'nullable',
            '*.note'                 => 'nullable|string',
            '*.is_active'            => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($request->all() as $row) {
                MeetingAttendance::updateOrCreate(
                    [
                        'meeting_id' => $row['meeting_id'],
                        'user_id'    => $row['user_id'],
                    ],
                    [
                        'attendance_type_id' => $row['attendance_type_id'],
                        'time'               => $row['time'],
                        'note'               => $row['note'] ?? null,
                        'is_active'          => $row['is_active'],
                        'updated_by'         => Auth::id(),
                        'created_by'         => Auth::id(),
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Attendance saved successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Failed to save attendance',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'meeting_id' => 'required',
            'user_id' => 'required',
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
            $meetingAttendances = MeetingAttendance::create([
                'meeting_id' => $request->meeting_id,
                'user_id' => $request->user_id,
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
    public function show(MeetingAttendance $meetingAttendance) {}
    public function edit(MeetingAttendance $meetingAttendance) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'meeting_id' => 'required',
            'user_id' => 'required',
            'attendance_type_id' => 'nullable',
            'date' => 'nullable',
            'time' => 'nullable',
            'note' => 'nullable',
            'is_active' => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $meetingAttendances = MeetingAttendance::find($id);
        if (!$meetingAttendances) {
            return response()->json(['status' => false, 'message' => 'Meeting Attendance not found.'], 404);
        }
        $meetingAttendances->update([
            'meeting_id' => $request->meeting_id,
            'user_id' => $request->user_id,
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
        $meetingAttendance = MeetingAttendance::find($id);
        if (!$meetingAttendance) {
            return response()->json(['status' => false, 'message' => 'Meeting Attendance member not found.'], 404);
        }
        $meetingAttendance->delete();
        return response()->json(['status' => true, 'message' => 'Meeting Attendance deleted successfully.'], 200);
    }
}
