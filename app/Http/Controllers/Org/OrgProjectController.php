<?php

namespace App\Http\Controllers\Org;
use App\Http\Controllers\Controller;
use App\Models\OrgProject;
use Illuminate\Http\Request;

class OrgProjectController extends Controller
{
    public function index($orgId)
    {
        $projectList = OrgProject::where('org_id', $orgId)
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
        OrgProject::create([
            'org_id' => $request->orgId,
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
        return response()->json(['message' => 'Project created successfully', 200]);
    }

    /**
     * Display the specified resource.
     */
    public function show(OrgProject $orgProject)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrgProject $orgProject)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrgProject $orgProject)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrgProject $orgProject)
    {
        //
    }
}
