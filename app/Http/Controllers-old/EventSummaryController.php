<?php

namespace App\Http\Controllers;

use App\Models\EventSummary;
use App\Models\EventSummaryFile;
use App\Models\EventSummaryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class EventSummaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $eventSummaries = EventSummary::all();
        return response()->json(['status' => true, 'data' => $eventSummaries], 200);
    }

     /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $eventSummary =  EventSummary::select('event_summaries.*', 'privacy_setups.id as privacy_id', 'privacy_setups.name as privacy_setup_name')
            ->leftJoin('privacy_setups', 'event_summaries.privacy_setup_id', '=', 'privacy_setups.id')
            ->with(['images', 'documents'])
            ->where('event_summaries.id', $id)->first();

        // Check if meeting exists
        if (!$eventSummary) {
            return response()->json(['status' => false, 'message' => 'Event Summary not found'], 404);
        }

        // Map over the images to include their full URLs
        $eventSummary->images = $eventSummary->images->map(function ($image) {
            $image->image_url = $image->file_path
                ? url(Storage::url($image->file_path))
                : null;
            return $image;
        });

        // Map over the documents to include their full URLs
        $eventSummary->documents = $eventSummary->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });


        // Return the meeting data
        return response()->json(['status' => true, 'data' => $eventSummary], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        dd($request->all());exit;
        // Validation
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|integer',
            'total_member_attendance' => 'required|integer',
            'total_guest_attendance' => 'required|integer',
            'summary' => 'nullable|string',
            'highlights' => 'nullable|string',
            'feedback' => 'nullable|string',
            'challenges' => 'nullable|string',
            'suggestions' => 'nullable|string',
            'financial_overview' => 'nullable|string',
            'total_expense' => 'required|numeric',
            'image_attachment' => 'nullable|file|mimes:jpg,jpeg,png',
            'file_attachment' => 'nullable|file|mimes:pdf,doc,docx',
            'next_steps' => 'nullable|string',
            'privacy_setup_id' => 'required|integer',
            'is_active' => 'boolean',
            'is_publish' => 'boolean',
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Handle image attachment upload
            $imageAttachmentPath = null;
            if ($request->hasFile('image_attachment')) {
                $image = $request->file('image_attachment');
                $imageAttachmentPath = $image->storeAs(
                    'event/images',
                    now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );
            }

            // Handle file attachment upload
            $fileAttachmentPath = null;
            if ($request->hasFile('file_attachment')) {
                $file = $request->file('file_attachment');
                $fileAttachmentPath = $file->storeAs(
                    'event/files',
                    now()->format('YmdHis') . '_' . $file->getClientOriginalName(),
                    'public'
                );
            }

            // Create new EventSummary record
            $eventSummary = new EventSummary();
            $eventSummary->event_id = $request->event_id;
            $eventSummary->total_member_attendance = $request->total_member_attendance;
            $eventSummary->total_guest_attendance = $request->total_guest_attendance;
            $eventSummary->summary = $request->summary;
            $eventSummary->highlights = $request->highlights;
            $eventSummary->feedback = $request->feedback;
            $eventSummary->challenges = $request->challenges;
            $eventSummary->suggestions = $request->suggestions;
            $eventSummary->financial_overview = $request->financial_overview;
            $eventSummary->total_expense = $request->total_expense;
            $eventSummary->image_attachment = $imageAttachmentPath;
            $eventSummary->file_attachment = $fileAttachmentPath;
            $eventSummary->next_steps = $request->next_steps;
            $eventSummary->privacy_setup_id = $request->privacy_setup_id;
            $eventSummary->is_active = $request->is_active;
            $eventSummary->is_publish = $request->is_publish;
            $eventSummary->updated_by = $request->user()->id;

            // Save the record
            $eventSummary->save();

             // Handle document uploads
             if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/doc/meeting-minute',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );

                    EventSummaryFile::create([
                        'event_summary_id' => $eventSummary->id,
                        'file_path' => $documentPath, // Store the document path
                        'file_name' => $document->getClientOriginalName(), // Store the document name
                        'mime_type' => $document->getClientMimeType(), // Store the MIME type
                        'file_size' => $document->getSize(), // Store the size of the document
                        'is_public' => true, // Set the document as public
                        'is_active' => true, // Set the document as active
                    ]);
                }
            }

            // // Handle multiple image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->storeAs(
                        'org/image/meeting-minute',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );

                    EventSummaryImage::create([
                        'event_summary_id' => $eventSummary->id,
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
                'data' => $eventSummary,
                'message' => 'Event summary created successfully!',
            ], 201);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error creating Event Summary: ' . $e->getMessage());

            // Return generic error response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|integer',
            'total_member_attendance' => 'required|integer',
            'total_guest_attendance' => 'required|integer',
            'summary' => 'nullable|string',
            'highlights' => 'nullable|string',
            'feedback' => 'nullable|string',
            'challenges' => 'nullable|string',
            'suggestions' => 'nullable|string',
            'financial_overview' => 'nullable|string',
            'total_expense' => 'required|numeric',
            // 'image_attachment' => 'nullable|file|mimes:jpg,jpeg,png',
            // 'file_attachment' => 'nullable|file|mimes:pdf,doc,docx',
            'next_steps' => 'nullable|string',
            'privacy_setup_id' => 'required|integer',
            'is_active' => 'boolean',
            'is_publish' => 'boolean',
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Find the existing EventSummary record
            $eventSummary = EventSummary::findOrFail($id);

            // Handle image attachment upload
            $imageAttachmentPath = $eventSummary->image_attachment; // Retain existing file if no new file is uploaded
            if ($request->hasFile('image_attachment')) {
                $image = $request->file('image_attachment');
                $imageAttachmentPath = $image->storeAs(
                    'event/images',
                    now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );
            }

            // Handle file attachment upload
            $fileAttachmentPath = $eventSummary->file_attachment; // Retain existing file if no new file is uploaded
            if ($request->hasFile('file_attachment')) {
                $file = $request->file('file_attachment');
                $fileAttachmentPath = $file->storeAs(
                    'event/files',
                    now()->format('YmdHis') . '_' . $file->getClientOriginalName(),
                    'public'
                );
            }

            // Update the EventSummary record
            $eventSummary->event_id = $request->event_id;
            $eventSummary->total_member_attendance = $request->total_member_attendance;
            $eventSummary->total_guest_attendance = $request->total_guest_attendance;
            $eventSummary->summary = $request->summary;
            $eventSummary->highlights = $request->highlights;
            $eventSummary->feedback = $request->feedback;
            $eventSummary->challenges = $request->challenges;
            $eventSummary->suggestions = $request->suggestions;
            $eventSummary->financial_overview = $request->financial_overview;
            $eventSummary->total_expense = $request->total_expense;
            $eventSummary->image_attachment = $imageAttachmentPath;
            $eventSummary->file_attachment = $fileAttachmentPath;
            $eventSummary->next_steps = $request->next_steps;
            $eventSummary->privacy_setup_id = $request->privacy_setup_id;
            $eventSummary->is_active = $request->is_active;
            $eventSummary->is_publish = $request->is_publish;
            $eventSummary->updated_by = $request->user()->id;

            // Save the updated record
            $eventSummary->save();

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/doc/meeting-minute',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );

                    EventSummaryFile::create([
                        'event_summary_id' => $eventSummary->id,
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

                    EventSummaryImage::create([
                        'event_summary_id' => $eventSummary->id,
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
                'data' => $eventSummary,
                'message' => 'Event summary updated successfully!',
            ], 200);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error updating Event Summary: ' . $e->getMessage());

            // Return generic error response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $eventSummary = EventSummary::findOrFail($id);

        $eventSummary->delete();
        return response()->json(['status' => true, 'message' => 'Meeting Attendance deleted successfully.'], 200);
    }
}
