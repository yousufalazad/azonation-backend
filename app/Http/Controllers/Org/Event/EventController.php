<?php
namespace App\Http\Controllers\Org\Event;
use App\Http\Controllers\Controller;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $user_id = $request->user()->id;
        $events = Event::where('user_id', $user_id)->get();
        return response()->json(['status' => true, 'data' => $events]);
    }
    public function getEvent($eventId)
    {
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $event], 200);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }
        $event = Event::create($request->all());
        return response()->json(['status' => true, 'message' => 'Event created successfully.', 'data' => $event], 201);
    }
    public function update(Request $request, $id)
    {
        $event = Event::find($id);
        if (!$event) {
            return response()->json(['status' => false, 'message' => 'Event not found.'], 404);
        }
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'name' => 'sometimes|required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }
        $event->update($request->all());
        return response()->json(['status' => true, 'message' => 'Event updated successfully.', 'data' => $event]);
    }
    public function destroy($id)
    {
        $event = Event::find($id);
        if (!$event) {
            return response()->json(['status' => false, 'message' => 'Event not found.'], 404);
        }
        $event->delete();
        return response()->json(['status' => true, 'message' => 'Event deleted successfully.']);
    }
}
