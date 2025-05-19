<?php
namespace App\Http\Controllers\Org\Project;
use App\Http\Controllers\Controller;
use App\Models\ProjectSummary;
use App\Models\ProjectSummaryFile;
use App\Models\ProjectSummaryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ProjectSummaryController extends Controller
{
    public function index()
    {
        $projectSummaries = ProjectSummary::all();
        return response()->json(['status' => true, 'data' => $projectSummaries], 200);
    }
    public function show($id)
    {
        $projectSummary =  ProjectSummary::select('project_summaries.*', 'privacy_setups.id as privacy_id', 'privacy_setups.name as privacy_setup_name')
            ->leftJoin('privacy_setups', 'project_summaries.privacy_setup_id', '=', 'privacy_setups.id')
            ->with(['images', 'documents'])
            ->where('project_summaries.id', $id)->first();
        if (!$projectSummary) {
            return response()->json(['status' => false, 'message' => 'Event Summary not found'], 404);
        }
         $projectSummary->images = $projectSummary->images->map(function ($image) {
            $image->image_url = $image->file_path
                ? url(Storage::url($image->file_path))
                : null;
            return $image;
        });
        $projectSummary->documents = $projectSummary->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });
        return response()->json(['status' => true, 'data' => $projectSummary], 200);
    }
    
    public function create() {}
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'org_project_id' => 'required|integer',
            'total_member_participation' => 'nullable|integer',
            'total_guest_participation' => 'nullable|integer',
            'total_participation' => 'nullable|integer',
            'total_beneficial_person' => 'nullable|integer',
            'total_communities_impacted' => 'nullable|integer',
            'total_expense' => 'nullable',
            'summary' => 'nullable|string',
            'highlights' => 'nullable|string',
            'feedback' => 'nullable|string',
            'challenges' => 'nullable|string',
            'suggestions' => 'nullable|string',
            'financial_overview' => 'nullable|string',
            'image_attachment' => 'nullable|file|mimes:jpg,jpeg,png',
            'file_attachment' => 'nullable|file|mimes:pdf,doc,docx',
            'next_steps' => 'nullable|string',
            'outcomes' => 'nullable|string',
            'privacy_setup_id' => 'nullable|integer',
            'is_active' => 'nullable',
            'is_publish' => 'nullable',
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
                    'project/images',
                    now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );
            }
            $fileAttachmentPath = null;
            if ($request->hasFile('file_attachment')) {
                $file = $request->file('file_attachment');
                $fileAttachmentPath = $file->storeAs(
                    'project/files',
                    now()->format('YmdHis') . '_' . $file->getClientOriginalName(),
                    'public'
                );
            }
            $projectSummary = new ProjectSummary();
            $projectSummary->org_project_id = $request->org_project_id;
            $projectSummary->total_member_participation = $request->total_member_participation;
            $projectSummary->total_guest_participation = $request->total_guest_participation;
            $projectSummary->total_participation = $request->total_participation;
            $projectSummary->total_beneficial_person = $request->total_beneficial_person;
            $projectSummary->total_communities_impacted = $request->total_communities_impacted;
            $projectSummary->summary = $request->summary;
            $projectSummary->highlights = $request->highlights;
            $projectSummary->feedback = $request->feedback;
            $projectSummary->challenges = $request->challenges;
            $projectSummary->suggestions = $request->suggestions;
            $projectSummary->financial_overview = $request->financial_overview;
            $projectSummary->total_expense = $request->total_expense;
            $projectSummary->image_attachment = $imageAttachmentPath;
            $projectSummary->file_attachment = $fileAttachmentPath;
            $projectSummary->next_steps = $request->next_steps;
            $projectSummary->outcomes = $request->outcomes;
            $projectSummary->privacy_setup_id = $request->privacy_setup_id;
            $projectSummary->is_active = $request->is_active;
            $projectSummary->is_publish = $request->is_publish;
            $projectSummary->updated_by = $request->user()->id;
            $projectSummary->save();
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/project-summary/file',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    ProjectSummaryFile::create([
                        'project_summary_id' => $projectSummary->id,
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
                        'org/project-summary/image',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    ProjectSummaryImage::create([
                        'project_summary_id' => $projectSummary->id,
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
                'data' => $projectSummary,
                'message' => 'Project summary created successfully!',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Project Summary: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }
    
    public function edit(ProjectSummary $projectSummary) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'org_project_id' => 'required|integer',
            'total_member_participation' => 'nullable|integer',
            'total_guest_participation' => 'nullable|integer',
            'total_participation' => 'nullable|integer',
            'total_beneficial_person' => 'nullable|integer',
            'total_communities_impacted' => 'nullable|integer',
            'total_expense' => 'nullable',
            'summary' => 'nullable|string',
            'highlights' => 'nullable|string',
            'feedback' => 'nullable|string',
            'challenges' => 'nullable|string',
            'suggestions' => 'nullable|string',
            'financial_overview' => 'nullable|string',
            'image_attachment' => 'nullable|file|mimes:jpg,jpeg,png',
            'file_attachment' => 'nullable|file|mimes:pdf,doc,docx',
            'next_steps' => 'nullable|string',
            'outcomes' => 'nullable|string',
            'privacy_setup_id' => 'nullable|integer',
            'is_active' => 'nullable',
            'is_publish' => 'nullable',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $fieldErrors = [];
            foreach ($errors->messages() as $field => $messages) {
                $fieldErrors[$field] = $messages[0];
            }
            return response()->json([
                'status' => false,
                'errors' => $fieldErrors,
            ], 422);
        }
        try {
            $projectSummary = ProjectSummary::findOrFail($id);
            $projectSummary->org_project_id = $request->org_project_id;
            $projectSummary->total_member_participation = $request->total_member_participation;
            $projectSummary->total_guest_participation = $request->total_guest_participation;
            $projectSummary->total_participation = $request->total_participation;
            $projectSummary->total_beneficial_person = $request->total_beneficial_person;
            $projectSummary->total_communities_impacted = $request->total_communities_impacted;
            $projectSummary->summary = $request->summary;
            $projectSummary->highlights = $request->highlights;
            $projectSummary->feedback = $request->feedback;
            $projectSummary->challenges = $request->challenges;
            $projectSummary->suggestions = $request->suggestions;
            $projectSummary->financial_overview = $request->financial_overview;
            $projectSummary->total_expense = $request->total_expense;
            $projectSummary->next_steps = $request->next_steps;
            $projectSummary->outcomes = $request->outcomes;
            $projectSummary->privacy_setup_id = $request->privacy_setup_id;
            $projectSummary->is_active = $request->is_active;
            $projectSummary->is_publish = $request->is_publish;
            $projectSummary->updated_by = $request->user()->id;
            $projectSummary->save();
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/project-summary/file',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    ProjectSummaryFile::create([
                        'project_summary_id' => $projectSummary->id,
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
                        'org/project-summary/image',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    ProjectSummaryImage::create([
                        'project_summary_id' => $projectSummary->id,
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
                'data' => $projectSummary,
                'message' => 'Project summary updated successfully!',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating Project Summary: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }
    public function destroy($id)
    {
        $projectSummary = ProjectSummary::findOrFail($id);
        $projectSummary->delete();
        return response()->json(['status' => true, 'message' => 'Meeting Attendance deleted successfully.'], 200);
    }
}
