<?php

namespace App\Http\Controllers\Org;
use App\Http\Controllers\Controller;

use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MeetingController extends Controller
{
    // Fetch all meetings
    public function index()
    {
        $meetings = Meeting::all();
        return response()->json(['status' => true, 'data' => $meetings]);
    }
    public function getOrgMeeting($userId)
    {
        $meetings = Meeting::where('user_id', $userId)->get();
        return response()->json(['status' => true, 'data' => $meetings]);
    }

    // Create a new meeting
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            // 'short_name' => 'required|string|max:100',
            // 'subject' => 'required|string|max:255',
            // 'date' => 'required|date',
            // 'time' => 'required|date_format:H:i',
            // 'description' => 'required|string',
            // 'address' => 'required|string',
            // 'agenda' => 'required|string|max:255',
            // 'requirements' => 'required|string|max:255',
            // 'note' => 'nullable|string|max:255',
            // 'status' => 'required|boolean',
            // 'conduct_type' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }

        $meeting = Meeting::create($request->all());
        return response()->json(['status' => true, 'message' => 'Meeting created successfully', 'data' => $meeting], 201);
    }

    // Get a specific meeting
    public function show($id)
    {
        $meeting = Meeting::find($id);

        if (!$meeting) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $meeting]);
    }

    // Update a meeting
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            // 'short_name' => 'required|string|max:100',
            // 'subject' => 'required|string|max:255',
            // 'date' => 'required|date',
            // 'time' => 'required|date_format:H:i',
            // 'description' => 'required|string',
            // 'address' => 'required|string',
            // 'agenda' => 'required|string|max:255',
            // 'requirements' => 'required|string|max:255',
            // 'note' => 'nullable|string|max:255',
            // 'status' => 'required|boolean',
            // 'conduct_type' => 'nullable|string|max:100',
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