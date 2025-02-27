<?php
namespace App\Http\Controllers\Org\Event;
use App\Http\Controllers\Controller;

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
    public function index()
    {
        $eventSummaries = EventSummary::all();
        return response()->json(['status' => true, 'data' => $eventSummaries], 200);
    }
    public function show($id)
    {
        $eventSummary =  EventSummary::select('event_summaries.*', 'privacy_setups.id as privacy_id', 'privacy_setups.name as privacy_setup_name')
            ->leftJoin('privacy_setups', 'event_summaries.privacy_setup_id', '=', 'privacy_setups.id')
            ->with(['images', 'documents'])
            ->where('event_summaries.id', $id)->first();
        if (!$eventSummary) {
            return response()->json(['status' => false, 'message' => 'Event Summary not found'], 404);
        }
        $eventSummary->images = $eventSummary->images->map(function ($image) {
            $image->image_url = $image->file_path
                ? url(Storage::url($image->file_path))
                : null;
            return $image;
        });
        $eventSummary->documents = $eventSummary->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });
        return response()->json(['status' => true, 'data' => $eventSummary], 200);
    }
    public function store(Request $request)
    {
        dd($request->all());
        exit;
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
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $imageAttachmentPath = null;
            if ($request->hasFile('image_attachment')) {
                $image = $request->file('image_attachment');
                $imageAttachmentPath = $image->storeAs(
                    'event/images',
                    now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );
            }
            $fileAttachmentPath = null;
            if ($request->hasFile('file_attachment')) {
                $file = $request->file('file_attachment');
                $fileAttachmentPath = $file->storeAs(
                    'event/files',
                    now()->format('YmdHis') . '_' . $file->getClientOriginalName(),
                    'public'
                );
            }
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
            $eventSummary->save();
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/doc/meeting-minute',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    EventSummaryFile::create([
                        'event_summary_id' => $eventSummary->id,
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
                        'org/image/meeting-minute',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    EventSummaryImage::create([
                        'event_summary_id' => $eventSummary->id,
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
                'data' => $eventSummary,
                'message' => 'Event summary created successfully!',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Event Summary: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
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
            'next_steps' => 'nullable|string',
            'privacy_setup_id' => 'required|integer',
            'is_active' => 'boolean',
            'is_publish' => 'boolean',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $eventSummary = EventSummary::findOrFail($id);
            $imageAttachmentPath = $eventSummary->image_attachment;
            if ($request->hasFile('image_attachment')) {
                $image = $request->file('image_attachment');
                $imageAttachmentPath = $image->storeAs(
                    'event/images',
                    now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );
            }
            $fileAttachmentPath = $eventSummary->file_attachment;
            if ($request->hasFile('file_attachment')) {
                $file = $request->file('file_attachment');
                $fileAttachmentPath = $file->storeAs(
                    'event/files',
                    now()->format('YmdHis') . '_' . $file->getClientOriginalName(),
                    'public'
                );
            }
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
            $eventSummary->save();
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/doc/meeting-minute',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    EventSummaryFile::create([
                        'event_summary_id' => $eventSummary->id,
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
                        'org/image/meeting-minute',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    EventSummaryImage::create([
                        'event_summary_id' => $eventSummary->id,
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
                'data' => $eventSummary,
                'message' => 'Event summary updated successfully!',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating Event Summary: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }
    public function destroy($id)
    {
        $eventSummary = EventSummary::findOrFail($id);
        $eventSummary->delete();
        return response()->json(['status' => true, 'message' => 'Meeting Attendance deleted successfully.'], 200);
    }
}
