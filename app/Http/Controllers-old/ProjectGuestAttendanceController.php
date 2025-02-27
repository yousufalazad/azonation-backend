<?php

namespace App\Http\Controllers;

use App\Models\ProjectGuestAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProjectGuestAttendanceController extends Controller
{
        /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projectAttendance = ProjectGuestAttendance::select('project_guest_attendances.*', 'attendance_types.name as attendance_types_name')
            ->leftJoin('attendance_types', 'project_guest_attendances.attendance_type_id', '=', 'attendance_types.id')
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
            'guest_name' => 'required',
            'about_guest' => 'nullable',
            'attendance_type_id' => 'required',
            'date' => 'nullable',
            'time' => 'nullable',
            'note' => 'nullable',
            'is_active' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Logging the inputs for debugging
            Log::info('project Attendance data: ', ['attendance_type_id' => $request->attendance_type_id, 'user_id' => $request->user_id]);

            // Create the project Attendance record
            $projectAttendance = ProjectGuestAttendance::create([
                'org_project_id' => $request->org_project_id,
                'guest_name' => $request->guest_name,
                'about_guest' => $request->about_guest,
                'attendance_type_id' => $request->attendance_type_id,
                'date' => $request->date,
                'time' => $request->time,
                'note' => $request->note,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $projectAttendance, 'message' => 'project Attendance created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating Country: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create project Attendance.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectGuestAttendance $guestProjectAttendance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProjectGuestAttendance $guestProjectAttendance)
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
            'guest_name' => 'required',
            'about_guest' => 'nullable',
            'attendance_type_id' => 'required',
            'date' => 'nullable',
            'time' => 'nullable',
            'note' => 'nullable',
            'is_active' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        // Find the project Attendances
        $projectAttendance = ProjectGuestAttendance::find($id);
        if (!$projectAttendance) {
            return response()->json(['status' => false, 'message' => 'project Attendance not found.'], 404);
        }

        // Update the project Attendances
        $projectAttendance->update([
            'org_project_id' => $request->org_project_id,
            'guest_name' => $request->guest_name,
            'about_guest' => $request->about_guest,
            'attendance_type_id' => $request->attendance_type_id,
            'date' => $request->date,
            'time' => $request->time,
            'note' => $request->note,
            'is_active' => $request->is_active,
        ]);

        return response()->json(['status' => true, 'data' => $projectAttendance, 'message' => 'project Attendance updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $projectAttendance = ProjectGuestAttendance::find($id);
        if (!$projectAttendance) {
            return response()->json(['status' => false, 'message' => 'project Attendance member not found.'], 404);
        }

        $projectAttendance->delete();
        return response()->json(['status' => true, 'message' => 'project Attendance deleted successfully.'], 200);
    }
}
