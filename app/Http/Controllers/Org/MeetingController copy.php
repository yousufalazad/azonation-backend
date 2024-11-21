<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;

use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MeetingController extends Controller
{
    // Fetch all meetings
    // public function index()
    // {
    //     $meetings = Meeting::all();
    //     return response()->json(['status' => true, 'data' => $meetings]);
    // }
    public function getOrgMeeting(Request $request)
    {
        $user_id = $request->user()->id; // Retrieve the authenticated user's ID
        $meetings = Meeting::select('meetings.*', 'conduct_types.id as conduct_type_id', 'conduct_types.name as conduct_type_name')
        ->leftJoin('conduct_types', 'meetings.conduct_type_id', '=', 'conduct_types.id')
        ->where('meetings.user_id', $user_id)->get();
        return response()->json(['status' => true, 'data' => $meetings]);
    }


    // Create a new meeting
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'subject' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'nullable|date_format:H:i',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'agenda' => 'nullable|string|max:255',
            'requirements' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
            'conduct_type_id' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }
        $input = $request->all();
        $input['user_id'] = $request->user()->id; // Assuming the user is authenticated and has an id. You'll need to modify this depending on your application.
        $meeting = Meeting::create($input);
        return response()->json(['status' => true, 'message' => 'Meeting created successfully', 'data' => $meeting], 201);
    }

    // Get a specific meeting
    public function show($id)
    {
        // Find the meeting by ID
        
        $meeting =  Meeting::select('meetings.*', 'conduct_types.id as conduct_type_id', 'conduct_types.name as conduct_type_name')
        ->leftJoin('conduct_types', 'meetings.conduct_type', '=', 'conduct_types.id')
        ->where('meetings.id', $id)->first();
        // $meeting = Meeting::find($id);

        // Check if meeting exists
        if (!$meeting) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }

        // Return the meeting data
        return response()->json(['status' => true, 'data' => $meeting], 200);
    }

    // Update a meeting
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'subject' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'nullable|date_format:H:i',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'agenda' => 'nullable|string|max:255',
            'requirements' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
            'conduct_type_id' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }

        $meeting = Meeting::find($id);
        if (!$meeting) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }

        $meeting->update($request->all());
        return response()->json(['status' => true, 'message' => 'Meeting updated successfully', 'data' => $meeting]);
    }
    
    // Delete a meeting
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
