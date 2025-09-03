<?php
namespace App\Http\Controllers\Org\Meeting;
use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingFile;
use App\Models\MeetingImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        $user_id = Auth::id();
        $meetings = Meeting::select('meetings.*', 'conduct_types.name as conduct_type_name')
            ->leftJoin('conduct_types', 'meetings.conduct_type_id', '=', 'conduct_types.id')
            ->where('meetings.user_id', $user_id)
            ->get();
        return response()->json(['status' => true, 'data' => $meetings]);
    }


    public function orgNextMeeting(Request $request)
    {
        $user_id = Auth::id();
    
        // Ensuring we're comparing against today in the same timezone
        $nextMeeting = Meeting::where('user_id', $user_id)
            ->whereDate('date', '>=', Carbon::today()->toDateString()) // Ensure to use date only
            ->orderBy('date', 'asc')
            ->first();

        if ($nextMeeting) {
            return response()->json([
                'status' => true,
                'data' => [
                    'date' => Carbon::parse($nextMeeting->date)->toDateString(),  // Format the date
                ]
            ], 200);
        }
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string',
            'subject' => 'nullable|string',
            'date' => 'nullable|date',
            // 'start_time' => 'nullable|date_format:H:i',
            'start_time' => 'nullable',
            // 'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'end_time' => 'nullable',
            'meeting_type' => 'nullable|string|max:50',
            'timezone' => 'nullable|string|max:50',
            'meeting_mode' => 'nullable|string',
            'duration' => 'nullable|integer',
            'priority' => 'nullable|string',
            'video_conference_link' => 'nullable|url',
            'access_code' => 'nullable|string|max:50',
            'recording_link' => 'nullable|url',
            'meeting_host' => 'nullable|string',
            'max_participants' => 'nullable|integer',
            'rsvp_status' => 'nullable',
            'rsvp_status.*' => 'string',
            'participants' => 'nullable',
            'participants.*' => 'string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'agenda' => 'nullable|string',
            'requirements' => 'nullable|string',
            'note' => 'nullable|string',
            'tags' => 'nullable',
            'tags.*' => 'string|max:50',
            'reminder_time' => 'nullable|integer',
            'repeat_frequency' => 'nullable|string',
            'attachment' => 'nullable|string|max:255',
            'conduct_type_id' => 'nullable|exists:conduct_types,id',
            'is_active' => 'nullable|boolean',
            'visibility' => 'nullable|string',
            'cancellation_reason' => 'nullable|string',
            'feedback_link' => 'nullable|url',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }
        $input = $request->all();
        $input['user_id'] = $request->user()->id;
        $input['created_by'] = $request->user()->id;
        $meeting = Meeting::create($input);
        
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentPath = $document->storeAs(
                    'org/meeting/file',
                    Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                    'public'
                );
                MeetingFile::create([
                    'meeting_id' => $meeting->id,
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
                    'org/meeting/image',
                    Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );
                MeetingImage::create([
                    'meeting_id' => $meeting->id,
                    'image_path' => $imagePath,
                    'file_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getClientMimeType(),
                    'file_size' => $image->getSize(),
                    'is_public' => true,
                    'is_active' => true,
                ]);
            }
        }
        return response()->json(['status' => true, 'message' => 'Meeting created successfully', 'data' => $meeting], 201);
    }
    public function show($id)
    {
        $meeting = Meeting::select('meetings.*', 'conduct_types.name as conduct_type_name')
            ->with(['images', 'documents'])
            ->leftJoin('conduct_types', 'meetings.conduct_type_id', '=', 'conduct_types.id')
            ->where('meetings.id', $id)
            ->first();
        if (!$meeting) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }
        $meeting->images = $meeting->images->map(function ($image) {
            $image->image_url = $image->image_path
                ? url(Storage::url($image->image_path))
                : null;
            return $image;
        });
        $meeting->documents = $meeting->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });
        return response()->json(['status' => true, 'data' => $meeting], 200);
    }
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string',
            'subject' => 'nullable|string',
            'date' => 'nullable|date',
            // 'start_time' => 'nullable|date_format:H:i',
            'start_time' => 'nullable',
            // 'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'end_time' => 'nullable',
            'meeting_type' => 'nullable|string|max:50',
            'timezone' => 'nullable|string|max:50',
            'meeting_mode' => 'nullable|string',
            'duration' => 'nullable|integer',
            'priority' => 'nullable|string',
            'video_conference_link' => 'nullable|url',
            'access_code' => 'nullable|string|max:50',
            'recording_link' => 'nullable|url',
            'meeting_host' => 'nullable|string',
            'max_participants' => 'nullable|integer',
            'rsvp_status' => 'nullable',
            'rsvp_status.*' => 'string',
            'participants' => 'nullable',
            'participants.*' => 'string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'agenda' => 'nullable|string',
            'requirements' => 'nullable|string',
            'note' => 'nullable|string',
            'tags' => 'nullable',
            'tags.*' => 'string|max:50',
            'reminder_time' => 'nullable|integer',
            'repeat_frequency' => 'nullable|string',
            'attachment' => 'nullable|string|max:255',
            'conduct_type_id' => 'nullable|exists:conduct_types,id',
            'is_active' => 'nullable|boolean',
            'visibility' => 'nullable|string',
            'cancellation_reason' => 'nullable|string',
            'feedback_link' => 'nullable|url',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }
        $meeting = Meeting::find($id);
        if (!$meeting) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }
        $input = $request->all();
        $input['updated_by'] = $request->user()->id;
        $meeting->update($input);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentPath = $document->storeAs(
                    'org/meeting/file',
                    Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                    'public'
                );
                MeetingFile::create([
                    'meeting_id' => $meeting->id,
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
                    'org/meeting/image',
                    Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );
                MeetingImage::create([
                    'meeting_id' => $meeting->id,
                    'image_path' => $imagePath,
                    'file_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getClientMimeType(),
                    'file_size' => $image->getSize(),
                    'is_public' => true,
                    'is_active' => true,
                ]);
            }
        }
        return response()->json(['status' => true, 'message' => 'Meeting updated successfully', 'data' => $meeting]);
    }
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
