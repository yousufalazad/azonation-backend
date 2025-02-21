<?php

namespace App\Http\Controllers\Org;
use App\Http\Controllers\Org\Validator;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{

    public function getProject($projectId)
    {
        // Find the Project by ID
        $project = Project::find($projectId);

        // Check if Project exists
        if (!$project) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }

        // Return the Project data
        return response()->json(['status' => true, 'data' => $project], 200);
    }

    public function index($userId)
    {
        $projectList = Project::where('user_id', $userId)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $projectList
        ]);
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
        $request->validate([
            'title' => 'required|string',
        ]);

        // Create a new event record associated with the organisation
        Project::create([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'venue_name' => $request->venue_name,
            'venue_address' => $request->venue_address,
            'requirements' => $request->requirements,
            'note' => $request->note,
            'status' => $request->status,
            'conduct_type' => $request->conduct_type,
        ]);

        // Return a success response
        return response()->json(['status' => true, 'message' => 'Project created successfully'], 200);
        // return response()->json(['status' => true, 'message' => 'Project created successfully', 'data' => $Project], 200);

    }

    /**
     * Display the specified resource.
     */
    public function show($projectId)
    {
        // Find the Project by ID
        $project = Project::find($projectId);

        // Check if Project exists
        if (!$project) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }

        // Return the Project data
        return response()->json(['status' => true, 'data' => $project], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Find the project by ID
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'status' => false,
                'message' => 'Project not found'
            ], 404);
        }

        // Update the project with the validated data
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
        $project->status = $request->input('status');
        $project->conduct_type = $request->input('conduct_type');

        // Save the updated project
        $project->save();

        // Return a success response
        return response()->json([
            'status' => true,
            'message' => 'Project updated successfully',
            'data' => $project
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }

        $project->delete();

        return response()->json(['status' => true, 'message' => 'Project deleted successfully']);
    }
}
