<?php

namespace App\Http\Controllers;
use App\Models\EventGuestAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class EventGuestAttendanceController extends Controller
{
    
        /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $eventAttendance = EventGuestAttendance::select('event_guest_attendances.*', 'attendance_types.name as attendance_types_name')
            ->leftJoin('attendance_types', 'event_guest_attendances.attendance_type_id', '=', 'attendance_types.id')
            ->get();
        return response()->json(['status' => true, 'data' => $eventAttendance], 200);
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
            'org_event_id' => 'required',
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
            Log::info('Event Attendance data: ', ['attendance_type_id' => $request->attendance_type_id, 'user_id' => $request->user_id]);

            // Create the Event Attendance record
            $eventAttendance = EventGuestAttendance::create([
                'org_event_id' => $request->org_event_id,
                'guest_name' => $request->guest_name,
                'about_guest' => $request->about_guest,
                'attendance_type_id' => $request->attendance_type_id,
                'date' => $request->date,
                'time' => $request->time,
                'note' => $request->note,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $eventAttendance, 'message' => 'Event Attendance created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating Country: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create Event Attendance.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EventGuestAttendance $guesteventAttendance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EventGuestAttendance $guesteventAttendance)
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
            'org_event_id' => 'required',
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
        // Find the Event Attendances
        $eventAttendance = EventGuestAttendance::find($id);
        if (!$eventAttendance) {
            return response()->json(['status' => false, 'message' => 'Event Attendance not found.'], 404);
        }

        // Update the Event Attendances
        $eventAttendance->update([
            'org_event_id' => $request->org_event_id,
            'guest_name' => $request->guest_name,
            'about_guest' => $request->about_guest,
            'attendance_type_id' => $request->attendance_type_id,
            'date' => $request->date,
            'time' => $request->time,
            'note' => $request->note,
            'is_active' => $request->is_active,
        ]);

        return response()->json(['status' => true, 'data' => $eventAttendance, 'message' => 'Event Attendance updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $eventAttendance = EventGuestAttendance::find($id);
        if (!$eventAttendance) {
            return response()->json(['status' => false, 'message' => 'Event Attendance member not found.'], 404);
        }

        $eventAttendance->delete();
        return response()->json(['status' => true, 'message' => 'Event Attendance deleted successfully.'], 200);
    }
}
