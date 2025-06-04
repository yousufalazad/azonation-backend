<?php

namespace App\Http\Controllers\Org\Event;

use App\Http\Controllers\Controller;

use App\Models\Event;
use App\Models\EventFile;
use App\Models\EventImage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        $event->images = $event->images->map(function ($image) {
            $image->image_url = $image->image_path
                ? url(Storage::url($image->image_path))
                : null;
            return $image;
        });
        $event->documents = $event->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });
        return response()->json(['status' => true, 'data' => $event], 200);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }
        $event = Event::create($request->all());
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentPath = $document->storeAs(
                    'org/event/file',
                    Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                    'public'
                );
                EventFile::create([
                    'event_id' => $event->id,
                    'file_path' => $documentPath,
                    'file_name' => $document->getClientOriginalName(),
                    'mime_type' => $document->getClientMimeType(),
                    'file_size' => $document->getSize(),
                    'is_public' => true,
                    'is_active' => true,
                ]);
            }
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = $image->storeAs(
                    'org/event/image',
                    Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );
                EventImage::create([
                    'event_id' => $event->id,
                    'image_path' => $imagePath,
                    'file_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getClientMimeType(),
                    'file_size' => $image->getSize(),
                    'is_public' => true,
                    'is_active' => true,
                ]);
            }
        }
        return response()->json(['status' => true, 'message' => 'Event created successfully.', 'data' => $event], 201);
    }
    public function update(Request $request, $id)
    {
        // dd($request->all());exit;
        // Validation
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'date' => 'nullable',
            'time' => 'nullable',
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'nullable|string',
            'requirements' => 'nullable|string',
            'note' => 'nullable|string',
            'status' => 'nullable',
            'conduct_type' => 'nullable|string|max:255',
        
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }

        $event = Event::find($id);
        if (!$event) {
            return response()->json(['status' => false, 'message' => 'Event not found.'], 404);
        }

        $input = $request->all();
        $input['user_id'] = $request->user()->id;
        $event->update($input);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentPath = $document->storeAs(
                    'org/event/file',
                    Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                    'public'
                );
                EventFile::create([
                    'event_id' => $event->id,
                    'file_path' => $documentPath,
                    'file_name' => $document->getClientOriginalName(),
                    'mime_type' => $document->getClientMimeType(),
                    'file_size' => $document->getSize(),
                    'is_public' => true,
                    'is_active' => true,
                ]);
            }
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = $image->storeAs(
                    'org/event/image',
                    Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );
                EventImage::create([
                    'event_id' => $event->id,
                    'image_path' => $imagePath,
                    'file_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getClientMimeType(),
                    'file_size' => $image->getSize(),
                    'is_public' => true,
                    'is_active' => true,
                ]);
            }
        }

        return response()->json(['status' => true, 'message' => 'Event updated successfully.', 'data' => $event]);
    }

    public function X_update(Request $request, $id)
    {
        $event = Event::find($id);
        if (!$event) {
            return response()->json(['status' => false, 'message' => 'Event not found.'], 404);
        }
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }
        $event->update($request->all());

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentPath = $document->storeAs(
                    'org/event/file',
                    Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                    'public'
                );
                EventFile::create([
                    'event_id' => $event->id,
                    'file_path' => $documentPath,
                    'file_name' => $document->getClientOriginalName(),
                    'mime_type' => $document->getClientMimeType(),
                    'file_size' => $document->getSize(),
                    'is_public' => true,
                    'is_active' => true,
                ]);
            }
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = $image->storeAs(
                    'org/event/image',
                    Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );
                EventImage::create([
                    'event_id' => $event->id,
                    'image_path' => $imagePath,
                    'file_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getClientMimeType(),
                    'file_size' => $image->getSize(),
                    'is_public' => true,
                    'is_active' => true,
                ]);
            }
        }
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
