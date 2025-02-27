<?php

namespace App\Http\Controllers\Org;

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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $meetingAttendance = MeetingMinutes::get();
        return response()->json(['status' => true, 'data' => $meetingAttendance], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

     /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $meetingMinute =  MeetingMinutes::select('meeting_minutes.*', 'privacy_setups.id as privacy_id', 'privacy_setups.name as privacy_setup_name')
            ->leftJoin('privacy_setups', 'meeting_minutes.privacy_setup_id', '=', 'privacy_setups.id')
            ->with(['images', 'documents'])
            ->where('meeting_minutes.id', $id)->first();

        // Check if meeting exists
        if (!$meetingMinute) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }

        // Map over the images to include their full URLs
        $meetingMinute->images = $meetingMinute->images->map(function ($image) {
            $image->image_url = $image->file_path
                ? url(Storage::url($image->file_path))
                : null;
            return $image;
        });

        // Map over the documents to include their full URLs
        $meetingMinute->documents = $meetingMinute->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });

        // Return the meeting data
        return response()->json(['status' => true, 'data' => $meetingMinute], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'meeting_id' => 'required|integer|exists:meetings,id',
            // 'prepared_by' => 'required|integer|exists:users,id',
            // 'reviewed_by' => 'required|integer|exists:users,id',
            'privacy_setup_id' => 'required|integer|exists:privacy_setups,id',
            'is_active' => 'required|boolean',
            // 'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Image validation for each file
            // 'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // Document validation
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        // Start transaction
        // DB::beginTransaction();
        // try {
        
            // Create a new MeetingMinutes meetingMinute
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

            // Save the meetingMinute to the database
            $meetingMinutes->save();

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/doc/meeting-minute',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );

                    MeetingMinuteFile::create([
                        'meeting_minute_id' => $meetingMinutes->id,
                        'file_path' => $documentPath, // Store the document path
                        'file_name' => $document->getClientOriginalName(), // Store the document name
                        'mime_type' => $document->getClientMimeType(), // Store the MIME type
                        'file_size' => $document->getSize(), // Store the size of the document
                        'is_public' => true, // Set the document as public
                        'is_active' => true, // Set the document as active
                    ]);
                }
            }

            // Handle multiple image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->storeAs(
                        'org/image/meeting-minute',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );

                    MeetingMinuteImage::create([
                        'meeting_minute_id' => $meetingMinutes->id,
                        'file_path' => $imagePath, // Store the document path
                        'file_name' => $image->getClientOriginalName(), // Store the document name
                        'mime_type' => $image->getClientMimeType(), // Store the MIME type
                        'file_size' => $image->getSize(), // Store the size of the document
                        'is_public' => true, // Set the document as public
                        'is_active' => true, // Set the document as active
                    ]);
                }
            }

            // Return success response
            return response()->json([
                'status' => true,
                'data' => $meetingMinutes,
                'message' => 'Meeting Minutes created successfully.'
            ], 201);
        // } catch (\Exception $e) {
        //     // Rollback transaction in case of error
        //     DB::rollBack();

        //     // Return an error response
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'An error occurred. Please try again.'
        //     ], 500);
        // }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MeetingMinutes $meetingMinutes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // dd( $request);exit;
        // Validation
        $validator = Validator::make($request->all(), [
            'meeting_id' => 'required|integer|exists:meetings,id',
            'prepared_by' => 'required|integer|exists:users,id',
            'reviewed_by' => 'required|integer|exists:users,id',
            'privacy_setup_id' => 'required|integer|exists:privacy_setups,id',
            'is_active' => 'required|boolean',
            'file_attachments' => 'nullable|file|mimes:pdf,doc,docx|max:1024', // Validate document file
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find the meetingMinute by ID
            $meetingMinutes = MeetingMinutes::findOrFail($id);

            // Handle the file upload
            if ($request->hasFile('file_attachments')) {
                //Delete the old file if exists
                if ($meetingMinutes->file_attachments && Storage::exists('public/' . $meetingMinutes->file_attachments)) {
                    Storage::delete('public/' . $meetingMinutes->file_attachments);
                }

                $file = $request->file('file_attachments');
                $fileAttachmentPath = $file->storeAs(
                    'org/docs',
                    now()->format('YmdHis') . '_' . $file->getClientOriginalName(),
                    'public'
                );

                $meetingMinutes->file_attachments = $fileAttachmentPath; // Update file path
            }

            // Update the MeetingMinutes meetingMinute
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

            // Save the updated meetingMinute
            $meetingMinutes->save();


            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/doc/meeting-minute',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );

                    MeetingMinuteFile::create([
                        'meeting_minute_id' => $meetingMinutes->id,
                        'file_path' => $documentPath, // Store the document path
                        'file_name' => $document->getClientOriginalName(), // Store the document name
                        'mime_type' => $document->getClientMimeType(), // Store the MIME type
                        'file_size' => $document->getSize(), // Store the size of the document
                        'is_public' => true, // Set the document as public
                        'is_active' => true, // Set the document as active
                    ]);
                }
            }

            // Handle multiple image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->storeAs(
                        'org/image/meeting-minute',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );

                    MeetingMinuteImage::create([
                        'meeting_minute_id' => $meetingMinutes->id,
                        'file_path' => $imagePath, // Store the document path
                        'file_name' => $image->getClientOriginalName(), // Store the document name
                        'mime_type' => $image->getClientMimeType(), // Store the MIME type
                        'file_size' => $image->getSize(), // Store the size of the document
                        'is_public' => true, // Set the document as public
                        'is_active' => true, // Set the document as active
                    ]);
                }
            }

            // Return success response
            return response()->json([
                'status' => true,
                'data' => $meetingMinutes,
                'message' => 'Meeting Minutes updated successfully.'
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error updating Meeting Minutes: ' . $e->getMessage());

            // Return generic error response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Find the meetingMinute by ID
            $meetingMinutes = MeetingMinutes::findOrFail($id);

            // Delete the file attachment if it exists
            if ($meetingMinutes->file_attachments && Storage::exists('public/' . $meetingMinutes->file_attachments)) {
                Storage::delete('public/' . $meetingMinutes->file_attachments);
            }

            // Delete the meetingMinute from the database
            $meetingMinutes->delete();

            // Return success response
            return response()->json([
                'status' => true,
                'message' => 'Meeting Minutes deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error deleting Meeting Minutes: ' . $e->getMessage());

            // Return generic error response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
}
