<?php

namespace App\Http\Controllers\Org\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\ProjectFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProjectController extends Controller
{
    public function getProject($projectId)
    {
        $project = Project::with(['images', 'documents'])
            ->where('id', $projectId)
            ->first();
        if (!$project) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }
        $project->images = $project->images->map(function ($image) {
            $image->image_url = $image->image_path
                ? url(Storage::url($image->image_path))
                : null;
            return $image;
        });
        $project->documents = $project->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });

        return response()->json(['status' => true, 'data' => $project], 200);
    }
    public function index(Request $request)
    {
        $user_id = $request->user()->id;
        $projectList = Project::where('user_id', $user_id)
            ->orderBy('id', 'asc')
            ->get();
        return response()->json([
            'status' => true,
            'data' => $projectList
        ]);
    }
    public function create() {}
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|string', // or use 'date_format:H:i' if you expect HH:MM
            'end_time' => 'nullable|string',
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'nullable|string',
            'requirements' => 'nullable|string',
            'note' => 'nullable|string',
            'is_active' => 'nullable|string|max:50',
            'conduct_type' => 'nullable|string|max:100',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,xlsx,xls,ppt,pptx,jpg,jpeg,png|max:100240',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:100240',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }
        $input = $request->all();
        $input['user_id'] = $request->user()->id;
        // $input['created_by'] = $request->user()->id;
        $project = Project::create($input);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentPath = $document->storeAs(
                    'org/project/file',
                    Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                    'public'
                );
                ProjectFile::create([
                    'project_id' => $project->id,
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
                    'org/project/image',
                    Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );
                ProjectImage::create([
                    'project_id' => $project->id,
                    'image_path' => $imagePath,
                    'file_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getClientMimeType(),
                    'file_size' => $image->getSize(),
                    'is_public' => true,
                    'is_active' => true,
                ]);
            }
        }

        return response()->json(['status' => true, 'message' => 'Project created successfully'], 200);
    }

    public function show($projectId)
    {
        $project = Project::with(['images', 'documents'])
            ->where('id', $projectId)
            ->first();
        if (!$project) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }
        $project->images = $project->images->map(function ($image) {
            $image->image_url = $image->image_path
                ? url(Storage::url($image->image_path))
                : null;
            return $image;
        });
        $project->documents = $project->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });

        return response()->json(['status' => true, 'data' => $project], 200);
    }
    public function edit(Project $project) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'nullable|string',
            'requirements' => 'nullable|string',
            'note' => 'nullable|string',
            'is_active' => 'nullable|string',
            'conduct_type' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }

        $project = Project::find($id);
        if (!$project) {
            return response()->json([ 'status' => false, 'message' => 'Project not found' ], 404);
        }

        $input = $request->all();
        $input['user_id'] = $request->user()->id;
        // $input['created_by'] = $request->user()->id;
        $project->update($input);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentPath = $document->storeAs(
                    'org/project/file',
                    Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                    'public'
                );
                ProjectFile::create([
                    'project_id' => $project->id,
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
                    'org/project/image',
                    Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );
                ProjectImage::create([
                    'project_id' => $project->id,
                    'image_path' => $imagePath,
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
            'message' => 'Project updated successfully',
            'data' => $project
        ], 200);
    }
    public function X_update(Request $request, $id)
    {
        $project = Project::find($id);
        if (!$project) {
            return response()->json([
                'status' => false,
                'message' => 'Project not found'
            ], 404);
        }
        $project->title = $request->input('title');
        $project->short_description = $request->input('short_description');
        $project->description = $request->input('description');
        $project->start_date = $request->input('start_date');
        $project->end_date = $request->input('end_date');
        $project->start_time = $request->input('start_time');
        $project->end_time = $request->input('end_time');
        $project->venue_name = $request->input('venue_name');
        $project->venue_address = $request->input('venue_address');
        $project->requirements = $request->input('requirements');
        $project->note = $request->input('note');
        $project->is_active = $request->input('is_active');
        $project->conduct_type = $request->input('conduct_type');
        $project->save();

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentPath = $document->storeAs(
                    'org/project/file',
                    Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                    'public'
                );
                ProjectFile::create([
                    'event_id' => $project->id,
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
                    'org/project/image',
                    Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );
                ProjectImage::create([
                    'event_id' => $project->id,
                    'image_path' => $imagePath,
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
            'message' => 'Project updated successfully',
            'data' => $project
        ], 200);
    }
    public function destroy($id)
    {
        $project = Project::find($id);
        if (!$project) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }
        $project->delete();
        return response()->json(['status' => true, 'message' => 'Project deleted successfully']);
    }
}
