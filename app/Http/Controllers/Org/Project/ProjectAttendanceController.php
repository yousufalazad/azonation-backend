<?php
namespace App\Http\Controllers\Org\Project;
use App\Http\Controllers\Controller;
use App\Models\ProjectAttendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProjectAttendanceController extends Controller
{
    public function getOrgUse()
    {
        $users = User::where('type', 'individual')->get();
        return response()->json(['status' => true, 'data' => $users], 200);
    }
    public function index()
    {
        $projectAttendance = ProjectAttendance::select('project_attendances.*', 'users.name as user_name', 'attendance_types.name as attendance_types_name')
            ->leftJoin('users', 'project_attendances.user_id', '=', 'users.id')
            ->leftJoin('attendance_types', 'project_attendances.attendance_type_id', '=', 'attendance_types.id')
            ->get();
        return response()->json(['status' => true, 'data' => $projectAttendance], 200);
    }
    public function create() {}
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'org_project_id' => 'required',
            'user_id' => 'required',
            'attendance_type_id' => 'required',
            'time' => 'nullable',
            'note' => 'nullable',
            'is_active' => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            Log::info('Meeting Attendance data: ', ['attendance_type_id' => $request->attendance_type_id, 'user_id' => $request->user_id]);
            $projectAttendances = ProjectAttendance::create([
                'org_project_id' => $request->org_project_id,
                'user_id' => $request->user_id,
                'attendance_type_id' => $request->attendance_type_id,
                'time' => $request->time,
                'note' => $request->note,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $projectAttendances, 'message' => 'Meeting Attendance created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Country: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create Meeting Attendance.'], 500);
        }
    }
    public function show(ProjectAttendance $projectAttendance) {}
    public function edit(ProjectAttendance $projectAttendance) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'org_project_id' => 'required',
            'user_id' => 'required',
            'attendance_type_id' => 'required',
            'time' => 'nullable',
            'note' => 'nullable',
            'is_active' => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $projectAttendances = ProjectAttendance::find($id);
        if (!$projectAttendances) {
            return response()->json(['status' => false, 'message' => 'Meeting Attendance not found.'], 404);
        }
        $projectAttendances->update([
            'org_project_id' => $request->org_project_id,
            'user_id' => $request->user_id,
            'attendance_type_id' => $request->attendance_type_id,
            'time' => $request->time,
            'note' => $request->note,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'data' => $projectAttendances, 'message' => 'Meeting Attendance updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $projectAttendance = ProjectAttendance::find($id);
        if (!$projectAttendance) {
            return response()->json(['status' => false, 'message' => 'Meeting Attendance member not found.'], 404);
        }
        $projectAttendance->delete();
        return response()->json(['status' => true, 'message' => 'Meeting Attendance deleted successfully.'], 200);
    }
}
