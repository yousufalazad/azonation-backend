<?php

namespace App\Http\Controllers;

use App\Models\ProjectSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ProjectSummaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projectSummaries = ProjectSummary::all();
        return response()->json(['status' => true, 'data' => $projectSummaries], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'org_project_id' => 'required|integer',
            'total_member_participation' => 'required|integer',
            'total_guest_participation' => 'required|integer',
            'total_participation' => 'required|integer',
            'total_beneficial_person' => 'required|integer',
            'total_communities_impacted' => 'required|integer',
            'total_expense' => 'required',

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
            'privacy_setup_id' => 'required|integer',
            'is_active' => 'required',
            'is_publish' => 'required',
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
            // $imageAttachmentPath = null;
            // if ($request->hasFile('image_attachment')) {
            //     $image = $request->file('image_attachment');
            //     $imageAttachmentPath = $image->storeAs(
            //         'project/images',
            //         now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
            //         'public'
            //     );
            // }

            // // Handle file attachment upload
            // $fileAttachmentPath = null;
            // if ($request->hasFile('file_attachment')) {
            //     $file = $request->file('file_attachment');
            //     $fileAttachmentPath = $file->storeAs(
            //         'project/files',
            //         now()->format('YmdHis') . '_' . $file->getClientOriginalName(),
            //         'public'
            //     );
            // }

            // Create new ProjectSummary record
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
            // $projectSummary->image_attachment = $imageAttachmentPath;
            // $projectSummary->file_attachment = $fileAttachmentPath;
            $projectSummary->next_steps = $request->next_steps;
            $projectSummary->outcomes = $request->outcomes;
            $projectSummary->privacy_setup_id = $request->privacy_setup_id;
            $projectSummary->is_active = $request->is_active;
            $projectSummary->is_publish = $request->is_publish;
            $projectSummary->updated_by = $request->user()->id;

            // Save the record
            $projectSummary->save();

            // Return success response
            return response()->json([
                'status' => true,
                'data' => $projectSummary,
                'message' => 'Project summary created successfully!',
            ], 201);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error creating Project Summary: ' . $e->getMessage());

            // Return generic error response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $projectSummary =  ProjectSummary::select('project_summaries.*', 'privacy_setups.id as privacy_id', 'privacy_setups.name as privacy_setup_name')
            ->leftJoin('privacy_setups', 'project_summaries.privacy_setup_id', '=', 'privacy_setups.id')
            ->where('project_summaries.id', $id)->first();

        // Check if meeting exists
        if (!$projectSummary) {
            return response()->json(['status' => false, 'message' => 'Event Summary not found'], 404);
        }

        // Return the meeting data
        return response()->json(['status' => true, 'data' => $projectSummary], 200);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProjectSummary $projectSummary)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'org_project_id' => 'required|integer',
            'total_member_participation' => 'required|integer',
            'total_guest_participation' => 'required|integer',
            'total_participation' => 'required|integer',
            'total_beneficial_person' => 'required|integer',
            'total_communities_impacted' => 'required|integer',
            'total_expense' => 'required',

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
            'privacy_setup_id' => 'required|integer',
            'is_active' => 'required',
            'is_publish' => 'required',
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            $errors = $validator->errors();
            $fieldErrors = [];
        
            // Iterate through each error field and message
            foreach ($errors->messages() as $field => $messages) {
                $fieldErrors[$field] = $messages[0]; // Return only the first error for each field
            }
        
            return response()->json([
                'status' => false,
                'errors' => $fieldErrors,
            ], 422);
        }

        try {
            // Find the existing ProjectSummary record
            $projectSummary = ProjectSummary::findOrFail($id);

            // Handle image attachment upload
            // $imageAttachmentPath = $projectSummary->image_attachment; // Retain existing file if no new file is uploaded
            // if ($request->hasFile('image_attachment')) {
            //     $image = $request->file('image_attachment');
            //     $imageAttachmentPath = $image->storeAs(
            //         'project/images',
            //         now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
            //         'public'
            //     );
            // }

            // Handle file attachment upload
            // $fileAttachmentPath = $projectSummary->file_attachment; // Retain existing file if no new file is uploaded
            // if ($request->hasFile('file_attachment')) {
            //     $file = $request->file('file_attachment');
            //     $fileAttachmentPath = $file->storeAs(
            //         'project/files',
            //         now()->format('YmdHis') . '_' . $file->getClientOriginalName(),
            //         'public'
            //     );
            // }

            // Update the ProjectSummary record
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
            // $projectSummary->image_attachment = $imageAttachmentPath;
            // $projectSummary->file_attachment = $fileAttachmentPath;
            $projectSummary->next_steps = $request->next_steps;
            $projectSummary->outcomes = $request->outcomes;
            $projectSummary->privacy_setup_id = $request->privacy_setup_id;
            $projectSummary->is_active = $request->is_active;
            $projectSummary->is_publish = $request->is_publish;
            $projectSummary->updated_by = $request->user()->id;

            // Save the record
            $projectSummary->save();


            // Return success response
            return response()->json([
                'status' => true,
                'data' => $projectSummary,
                'message' => 'Project summary updated successfully!',
            ], 200);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error updating Project Summary: ' . $e->getMessage());

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
        $projectSummary = ProjectSummary::findOrFail($id);

        $projectSummary->delete();
        return response()->json(['status' => true, 'message' => 'Meeting Attendance deleted successfully.'], 200);
    }
}