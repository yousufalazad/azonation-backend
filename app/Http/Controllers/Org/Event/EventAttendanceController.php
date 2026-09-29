<?php
namespace App\Http\Controllers\Org\Event;

use Illuminate\Support\Facades\DB;
use App\Http\Concerns\ResolvesCurrentOrg;
use App\Models\Event;
// use App\Http\Controllers\Controller;
use Illuminate\Routing\Controller;

use App\Models\EventAttendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class EventAttendanceController extends Controller
{
    use ResolvesCurrentOrg;

    public function __construct()
    {
        $this->middleware('org.permission:event-attendance.read')->only(['index', 'show']);
        $this->middleware('org.permission:event-attendance.create')->only(['create', 'store']);
        $this->middleware('org.permission:event-attendance.update')->only(['edit', 'update']);
        $this->middleware('org.permission:event-attendance.delete')->only(['destroy']);
    }
    public function index()
    {
        $eventAttendance = $this->ownedVia(EventAttendance::class, 'event_id', Event::class)->select('event_attendances.*', DB::raw("CONCAT_WS(' ', users.first_name, users.last_name) as user_name"), 'attendance_types.name as attendance_types_name')
            ->leftJoin('users', 'event_attendances.user_id', '=', 'users.id')
            ->leftJoin('attendance_types', 'event_attendances.attendance_type_id', '=', 'attendance_types.id')
            ->get();
        return response()->json(['status' => true, 'data' => $eventAttendance], 200);
    }
    public function create() {}
    public function store(Request $request)
    {
        // The event must belong to this organisation
        $this->ensureOwnedParent(Event::class, $request->input('event_id'));

        $validator = Validator::make($request->all(), [
            'event_id' => 'required',
            'attendance_type_id' => 'nullable',
            'time' => 'nullable',
            'note' => 'nullable',
            'is_active' => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            Log::info('Meeting Attendance data: ', ['attendance_type_id' => $request->attendance_type_id, 'user_id' => $request->user_id]);
            $eventAttendances = EventAttendance::create([
                'event_id' => $request->event_id,
                'user_id' => $request->user_id,
                'attendance_type_id' => $request->attendance_type_id,
                'time' => $request->time,
                'note' => $request->note,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $eventAttendances, 'message' => 'Meeting Attendance created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Country: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create Meeting Attendance.'], 500);
        }
    }
    public function show(EventAttendance $eventAttendance) {}
    public function edit(EventAttendance $eventAttendance) {}
    public function update(Request $request, $id)
    {
        // The event must belong to this organisation
        if ($request->has('event_id')) $this->ensureOwnedParent(Event::class, $request->input('event_id'));

        $validator = Validator::make($request->all(), [
            'event_id' => 'required',
            'user_id' => 'required',
            'attendance_type_id' => 'nullable',
            'time' => 'nullable',
            'note' => 'nullable',
            'is_active' => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $eventAttendances = $this->ownedVia(EventAttendance::class, 'event_id', Event::class)->find($id);
        if (!$eventAttendances) {
            return response()->json(['status' => false, 'message' => 'Meeting Attendance not found.'], 404);
        }
        $eventAttendances->update([
            'event_id' => $request->event_id,
            'user_id' => $request->user_id,
            'attendance_type_id' => $request->attendance_type_id,
            'time' => $request->time,
            'note' => $request->note,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'data' => $eventAttendances, 'message' => 'Meeting Attendance updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $eventAttendance = $this->ownedVia(EventAttendance::class, 'event_id', Event::class)->find($id);
        if (!$eventAttendance) {
            return response()->json(['status' => false, 'message' => 'Meeting Attendance member not found.'], 404);
        }
        $eventAttendance->delete();
        return response()->json(['status' => true, 'message' => 'Meeting Attendance deleted successfully.'], 200);
    }
}
