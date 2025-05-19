<?php
namespace App\Http\Controllers\Org\Meeting;
use App\Http\Controllers\Controller;
use App\Models\MeetingMinutes;
use App\Models\MeetingMinuteFile;
use App\Models\MeetingMinuteImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MeetingMinutesController extends Controller
{
    public function index()
    {
        $meetingAttendance = MeetingMinutes::get();
        return response()->json(['status' => true, 'data' => $meetingAttendance], 200);
    }
    public function create() {}
    public function show($id)
    {
        $meetingMinute =  MeetingMinutes::select('meeting_minutes.*', 'privacy_setups.id as privacy_id', 'privacy_setups.name as privacy_setup_name')
            ->leftJoin('privacy_setups', 'meeting_minutes.privacy_setup_id', '=', 'privacy_setups.id')
            ->with(['images', 'documents'])
            ->where('meeting_minutes.id', $id)->first();
        if (!$meetingMinute) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }
        $meetingMinute->images = $meetingMinute->images->map(function ($image) {
            $image->image_url = $image->file_path
                ? url(Storage::url($image->file_path))
                : null;
            return $image;
        });
        $meetingMinute->documents = $meetingMinute->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });

        // dd($meetingMinute);exit;
        return response()->json(['status' => true, 'data' => $meetingMinute], 200);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'meeting_id' => 'required|integer|exists:meetings,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        $meetingMinutes = new MeetingMinutes();
        $meetingMinutes->meeting_id = $request->meeting_id;
        $meetingMinutes->prepared_by = $request->user()->id;
        $meetingMinutes->reviewed_by = $request->user()->id;
        $meetingMinutes->minutes = $request->minutes;
        $meetingMinutes->decisions = $request->decisions;
        $meetingMinutes->note = $request->note;
        $meetingMinutes->start_time = $request->start_time;
        $meetingMinutes->end_time = $request->end_time;
        $meetingMinutes->follow_up_tasks = $request->follow_up_tasks;
        $meetingMinutes->tags = $request->tags;
        $meetingMinutes->action_items = $request->action_items;
        $meetingMinutes->meeting_location = $request->meeting_location;
        $meetingMinutes->video_link = $request->video_link;
        $meetingMinutes->privacy_setup_id = $request->privacy_setup_id;
        $meetingMinutes->approval_status = $request->approval_status;
        $meetingMinutes->is_publish = $request->is_publish;
        $meetingMinutes->is_active = $request->is_active;
        $meetingMinutes->save();
        // if ($request->hasFile('documents')) {
        //     foreach ($request->file('documents') as $document) {
        //         $documentPath = $document->storeAs(
        //             'org/meeting-minute/file',
        //             Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
        //             'public'
        //         );
        //         MeetingMinuteFile::create([
        //             'meeting_minute_id' => $meetingMinutes->id,
        //             'file_path' => $documentPath,
        //             'file_name' => $document->getClientOriginalName(),
        //             'mime_type' => $document->getClientMimeType(),
        //             'file_size' => $document->getSize(),
        //             'is_public' => true,
        //             'is_active' => true,
        //         ]);
        //     }
        // }
        // if ($request->hasFile('images')) {
        //     foreach ($request->file('images') as $image) {
        //         $imagePath = $image->storeAs(
        //             'org/meeting-minute/image',
        //             Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
        //             'public'
        //         );
        //         MeetingMinuteImage::create([
        //             'meeting_minute_id' => $meetingMinutes->id,
        //             'file_path' => $imagePath,
        //             'file_name' => $image->getClientOriginalName(),
        //             'mime_type' => $image->getClientMimeType(),
        //             'file_size' => $image->getSize(),
        //             'is_public' => true,
        //             'is_active' => true,
        //         ]);
        //     }
        // }
        return response()->json([
            'status' => true,
            'data' => $meetingMinutes,
            'message' => 'Meeting Minutes created successfully.'
        ], 201);
    }
    public function edit(MeetingMinutes $meetingMinutes) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'meeting_id' => 'required|integer|exists:meetings,id',
            // 'prepared_by' => 'required|integer|exists:users,id',
            // 'reviewed_by' => 'required|integer|exists:users,id',
            // 'privacy_setup_id' => 'required|integer|exists:privacy_setups,id',
            // 'is_active' => 'required|boolean',
            // 'file_attachments' => 'nullable|file|mimes:pdf,doc,docx|max:1024',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            $meetingMinutes = MeetingMinutes::findOrFail($id);
            // if ($request->hasFile('file_attachments')) {
            //     if ($meetingMinutes->file_attachments && Storage::exists('public/' . $meetingMinutes->file_attachments)) {
            //         Storage::delete('public/' . $meetingMinutes->file_attachments);
            //     }
            //     $file = $request->file('file_attachments');
            //     $fileAttachmentPath = $file->storeAs(
            //         'org/meeting-minute/file',
            //         now()->format('YmdHis') . '_' . $file->getClientOriginalName(),
            //         'public'
            //     );
            //     $meetingMinutes->file_attachments = $fileAttachmentPath;
            // }
            $meetingMinutes->meeting_id = $request->meeting_id;
            $meetingMinutes->prepared_by = $request->prepared_by;
            $meetingMinutes->reviewed_by = $request->reviewed_by;
            $meetingMinutes->minutes = $request->minutes;
            $meetingMinutes->decisions = $request->decisions;
            $meetingMinutes->note = $request->note;
            $meetingMinutes->start_time = $request->start_time;
            $meetingMinutes->end_time = $request->end_time;
            $meetingMinutes->follow_up_tasks = $request->follow_up_tasks;
            $meetingMinutes->tags = $request->tags;
            $meetingMinutes->action_items = $request->action_items;
            $meetingMinutes->meeting_location = $request->meeting_location;
            $meetingMinutes->video_link = $request->video_link;
            $meetingMinutes->privacy_setup_id = $request->privacy_setup_id;
            $meetingMinutes->approval_status = $request->approval_status;
            $meetingMinutes->is_publish = $request->is_publish;
            $meetingMinutes->is_active = $request->is_active;
            $meetingMinutes->save();
            // if ($request->hasFile('documents')) {
            //     foreach ($request->file('documents') as $document) {
            //         $documentPath = $document->storeAs(
            //             'org/meeting-minute/file',
            //             Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
            //             'public'
            //         );
            //         MeetingMinuteFile::create([
            //             'meeting_minute_id' => $meetingMinutes->id,
            //             'file_path' => $documentPath,
            //             'file_name' => $document->getClientOriginalName(),
            //             'mime_type' => $document->getClientMimeType(),
            //             'file_size' => $document->getSize(),
            //             'is_public' => true,
            //             'is_active' => true,
            //         ]);
            //     }
            // }
            // if ($request->hasFile('images')) {
            //     foreach ($request->file('images') as $image) {
            //         $imagePath = $image->storeAs(
            //             'org/meeting-minute/image',
            //             Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
            //             'public'
            //         );
            //         MeetingMinuteImage::create([
            //             'meeting_minute_id' => $meetingMinutes->id,
            //             'file_path' => $imagePath,
            //             'file_name' => $image->getClientOriginalName(),
            //             'mime_type' => $image->getClientMimeType(),
            //             'file_size' => $image->getSize(),
            //             'is_public' => true,
            //             'is_active' => true,
            //         ]);
            //     }
            // }

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/meeting-minute/file',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    MeetingMinuteFile::create([
                        'meeting_minute_id' => $meetingMinutes->id,
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
                        'org/meeting-minute/image',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    MeetingMinuteImage::create([
                        'meeting_minute_id' => $meetingMinutes->id,
                        'file_path' => $imagePath,
                        'file_name' => $image->getClientOriginalName(),
                        'mime_type' => $image->getClientMimeType(),
                        'file_size' => $image->getSize(),
                        'is_public' => true,
                        'is_active' => true,
                    ]);
                }
            }
            return response()->json([
                'status' => true,
                'data' => $meetingMinutes,
                'message' => 'Meeting Minutes updated successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating Meeting Minutes: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $meetingMinutes = MeetingMinutes::findOrFail($id);
            if ($meetingMinutes->file_attachments && Storage::exists('public/' . $meetingMinutes->file_attachments)) {
                Storage::delete('public/' . $meetingMinutes->file_attachments);
            }
            $meetingMinutes->delete();
            return response()->json([
                'status' => true,
                'message' => 'Meeting Minutes deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting Meeting Minutes: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
}
