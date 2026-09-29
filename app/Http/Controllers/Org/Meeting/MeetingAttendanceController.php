<?php
namespace App\Http\Controllers\Org\Meeting;

use App\Http\Concerns\ResolvesCurrentOrg;
use App\Models\AttendanceStatus;
use App\Models\Meeting;
use Illuminate\Routing\Controller;

use App\Models\MeetingAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Member attendance at a meeting. Each record has:
 *  - attendance_status_id: what happened (Present, Late, Absent...), required
 *  - attendance_type_id:   how they took part (In Person, Online...), required when the
 *                          status means they attended, empty when they did not
 */
class MeetingAttendanceController extends Controller
{
    use ResolvesCurrentOrg;

    public function __construct()
    {
        $this->middleware('org.permission:meeting-attendance.read')->only(['index', 'show']);
        $this->middleware('org.permission:meeting-attendance.create')->only(['create', 'store', 'bulkStore']);
        $this->middleware('org.permission:meeting-attendance.update')->only(['edit', 'update']);
        $this->middleware('org.permission:meeting-attendance.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $meetingAttendance = $this->ownedVia(MeetingAttendance::class, 'meeting_id', Meeting::class)
            ->select(
                'meeting_attendances.*',
                'users.first_name as user_first_name',
                'users.last_name as user_last_name',
                'attendance_types.name as attendance_types_name',
                'attendance_statuses.name as attendance_status_name',
                'attendance_statuses.is_attended as is_attended'
            )
            ->leftJoin('users', 'meeting_attendances.user_id', '=', 'users.id')
            ->leftJoin('attendance_types', 'meeting_attendances.attendance_type_id', '=', 'attendance_types.id')
            ->leftJoin('attendance_statuses', 'meeting_attendances.attendance_status_id', '=', 'attendance_statuses.id')
            // ?meeting_id=5 returns one meeting's attendance only
            ->when($request->query('meeting_id'), fn ($q, $meetingId) => $q->where('meeting_attendances.meeting_id', $meetingId))
            ->get();
        return response()->json(['status' => true, 'data' => $meetingAttendance], 200);
    }

    public function create() {}

    /**
     * Save many members' attendance at once (insert or update per member and meeting)
     */
    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '*.meeting_id'           => 'required|exists:meetings,id',
            '*.user_id'              => 'required|exists:users,id',
            '*.attendance_status_id' => 'required|exists:attendance_statuses,id',
            '*.attendance_type_id'   => 'nullable|exists:attendance_types,id',
            '*.time'                 => 'nullable',
            '*.note'                 => 'nullable|string',
            '*.is_active'            => 'required|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        // Every meeting in the list must belong to this organisation
        foreach (collect($request->all())->pluck('meeting_id')->unique() as $meetingId) {
            $this->ensureOwnedParent(Meeting::class, $meetingId);
        }

        $attended = $this->attendedMap();
        foreach ($request->all() as $i => $row) {
            if ($attended[$row['attendance_status_id']] && empty($row['attendance_type_id'])) {
                return $this->needTypeResponse("$i.attendance_type_id");
            }
        }

        DB::beginTransaction();
        try {
            foreach ($request->all() as $row) {
                $didAttend = $attended[$row['attendance_status_id']];
                MeetingAttendance::updateOrCreate(
                    ['meeting_id' => $row['meeting_id'], 'user_id' => $row['user_id']],
                    [
                        'attendance_status_id' => $row['attendance_status_id'],
                        // No "how" for someone who was not there
                        'attendance_type_id'   => $didAttend ? $row['attendance_type_id'] : null,
                        'time'                 => $didAttend ? ($row['time'] ?? null) : null,
                        'note'                 => $row['note'] ?? null,
                        // enum('0','1') column: a bare 1/true would pick the first value, '0'
                        'is_active'            => filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
                        'updated_by'           => Auth::id(),
                        'created_by'           => Auth::id(),
                    ]
                );
            }
            DB::commit();
            return response()->json(['status' => true, 'message' => 'Attendance saved successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Failed to save attendance',
                'error'   => \App\Support\ErrorDetail::for($e),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // The meeting must belong to this organisation
        $this->ensureOwnedParent(Meeting::class, $request->input('meeting_id'));

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        $didAttend = $this->attendedMap()[$request->attendance_status_id];
        if ($didAttend && !$request->attendance_type_id) {
            return $this->needTypeResponse('attendance_type_id');
        }
        try {
            $meetingAttendances = MeetingAttendance::create($this->values($request, $didAttend));
            return response()->json(['status' => true, 'data' => $meetingAttendances, 'message' => 'Meeting Attendance created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Meeting Attendance: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create Meeting Attendance.'], 500);
        }
    }

    public function show(MeetingAttendance $meetingAttendance) {}
    public function edit(MeetingAttendance $meetingAttendance) {}

    public function update(Request $request, $id)
    {
        // The meeting must belong to this organisation
        if ($request->has('meeting_id')) $this->ensureOwnedParent(Meeting::class, $request->input('meeting_id'));

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        $meetingAttendances = $this->ownedVia(MeetingAttendance::class, 'meeting_id', Meeting::class)->find($id);
        if (!$meetingAttendances) {
            return response()->json(['status' => false, 'message' => 'Meeting Attendance not found.'], 404);
        }
        $didAttend = $this->attendedMap()[$request->attendance_status_id];
        if ($didAttend && !$request->attendance_type_id) {
            return $this->needTypeResponse('attendance_type_id');
        }
        $meetingAttendances->update($this->values($request, $didAttend));
        return response()->json(['status' => true, 'data' => $meetingAttendances, 'message' => 'Meeting Attendance updated successfully.'], 200);
    }

    public function destroy($id)
    {
        $meetingAttendance = $this->ownedVia(MeetingAttendance::class, 'meeting_id', Meeting::class)->find($id);
        if (!$meetingAttendance) {
            return response()->json(['status' => false, 'message' => 'Meeting Attendance member not found.'], 404);
        }
        $meetingAttendance->delete();
        return response()->json(['status' => true, 'message' => 'Meeting Attendance deleted successfully.'], 200);
    }

    private function rules(): array
    {
        return [
            'meeting_id' => 'required|exists:meetings,id',
            'user_id' => 'required|exists:users,id',
            'attendance_status_id' => 'required|exists:attendance_statuses,id',
            'attendance_type_id' => 'nullable|exists:attendance_types,id',
            'date' => 'nullable|date',
            'time' => 'nullable',
            'note' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }

    private function values(Request $request, bool $didAttend): array
    {
        return [
            'meeting_id' => $request->meeting_id,
            'user_id' => $request->user_id,
            'attendance_status_id' => $request->attendance_status_id,
            'attendance_type_id' => $didAttend ? $request->attendance_type_id : null,
            'date' => $request->date,
            'time' => $didAttend ? $request->time : null,
            'note' => $request->note,
            'is_active' => $request->has('is_active') && !$request->boolean('is_active') ? '0' : '1',
        ];
    }

    // status id => did the person take part
    private function attendedMap(): array
    {
        return AttendanceStatus::pluck('is_attended', 'id')->map(fn ($v) => (bool) $v)->all();
    }

    private function needTypeResponse(string $field)
    {
        return response()->json([
            'status' => false,
            'message' => 'Choose how the person attended (for example In Person or Online).',
            'errors' => [$field => ['Choose how the person attended.']],
        ], 422);
    }
}
