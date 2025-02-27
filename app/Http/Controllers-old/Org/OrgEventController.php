<?php
namespace App\Http\Controllers\Org;
use App\Http\Controllers\Controller;

use App\Models\OrgEvent; // Ensure this is the model for your OrgEvents
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrgEventController extends Controller
{
    // Fetch all OrgEvents
    public function index($userId)
    {
        $events = OrgEvent::where('user_id', $userId)->get();

        return response()->json(['status' => true, 'data' => $events]);
    }
    public function getEvent($eventId)
    {
        // Find the meeting by ID
        $event = OrgEvent::find($eventId);

        // Check if meeting exists
        if (!$event) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }

        // Return the meeting data
        return response()->json(['status' => true, 'data' => $event], 200);
    }

    // Create a new OrgEvent
    public function createEvent(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            // 'short_description' => 'nullable|string|max:500',
            // 'description' => 'nullable|string',
            // 'date' => 'required|date',
            // 'time' => 'required|date_format:H:i',
            // 'venue_name' => 'nullable|string|max:255',
            // 'venue_address' => 'nullable|string|max:255',
            // 'requirements' => 'nullable|string',
            // 'note' => 'nullable|string',
            // 'status' => 'required|integer|in:0,1', // 0 for Active, 1 for Disabled
            // 'conduct_type' => 'required|integer|in:1,2', // 1 for In Person, 2 for Online
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }

        // Create the event
        $event = OrgEvent::create($request->all());

        return response()->json(['status' => true, 'message' => 'Event created successfully.', 'data' => $event], 201);
    }

    // Update an existing event
    public function updateEvent(Request $request, $id)
    {
        $event = OrgEvent::find($id);
        if (!$event) {
            return response()->json(['status' => false, 'message' => 'Event not found.'], 404);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'name' => 'sometimes|required|string|max:255',
            // 'short_description' => 'nullable|string|max:500',
            // 'description' => 'nullable|string',
            // 'date' => 'sometimes|required|date',
            // 'time' => 'sometimes|required|date_format:H:i',
            // 'venue_name' => 'nullable|string|max:255',
            // 'venue_address' => 'nullable|string|max:255',
            // 'requirements' => 'nullable|string',
            // 'note' => 'nullable|string',
            // 'status' => 'sometimes|required|integer|in:0,1',
            // 'conduct_type' => 'sometimes|required|integer|in:1,2',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }

        // Update the event
        $event->update($request->all());

        return response()->json(['status' => true, 'message' => 'Event updated successfully.', 'data' => $event]);
    }

    // Delete an event
    public function deleteEvent($id)
    {
        $event = OrgEvent::find($id);
        if (!$event) {
            return response()->json(['status' => false, 'message' => 'Event not found.'], 404);
        }

        $event->delete();

        return response()->json(['status' => true, 'message' => 'Event deleted successfully.']);
    }
}
