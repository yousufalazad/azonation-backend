<?php

namespace App\Http\Controllers;

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
            'org_project_id' => 'required',
            'user_id' => 'required',
            'attendance_type_id' => 'required',
            // 'date' => 'nullable',
            'time' => 'nullable',
            'note' => 'nullable',
            'is_active' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Logging the inputs for debugging
            Log::info('Meeting Attendance data: ', ['attendance_type_id' => $request->attendance_type_id, 'user_id' => $request->user_id]);

            // Create the Meeting Attendance record
            $projectAttendances = ProjectAttendance::create([
                'org_project_id' => $request->org_project_id,
                'user_id' => $request->user_id,
                'attendance_type_id' => $request->attendance_type_id,
                // 'date' => $request->date,
                'time' => $request->time,
                'note' => $request->note,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $projectAttendances, 'message' => 'Meeting Attendance created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating Country: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create Meeting Attendance.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectAttendance $projectAttendance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProjectAttendance $projectAttendance)
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
            'org_project_id' => 'required',
            'user_id' => 'required',
            'attendance_type_id' => 'required',
            // 'date' => 'nullable',
            'time' => 'nullable',
            'note' => 'nullable',
            'is_active' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        // Find the Meeting Attendances
        $projectAttendances = ProjectAttendance::find($id);
        if (!$projectAttendances) {
            return response()->json(['status' => false, 'message' => 'Meeting Attendance not found.'], 404);
        }

        // Update the Meeting Attendances
        $projectAttendances->update([
            'org_project_id' => $request->org_project_id,
            'user_id' => $request->user_id,
            'attendance_type_id' => $request->attendance_type_id,
            // 'date' => $request->date,
            'time' => $request->time,
            'note' => $request->note,
            'is_active' => $request->is_active,
        ]);

        return response()->json(['status' => true, 'data' => $projectAttendances, 'message' => 'Meeting Attendance updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
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
