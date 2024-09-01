<?php

namespace App\Http\Controllers\Org;
use App\Http\Controllers\Controller;
use App\Models\CommitteeName;
use Illuminate\Http\Request;

class CommitteeNameController extends Controller
{


    public function getCommitteeListByOrgId($orgId)
    {
        $committeeList = CommitteeName::where('org_id', $orgId)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $committeeList
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */



    /**
     * Store a newly created resource in storage.
     */
    public function create()
    {
        //
    }
    public function Store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        // Create a new committee record associated with the organisation
        CommitteeName::create([
            'org_id' => $request->orgId,
            'name' => $request->name,
            'short_description' => $request->short_description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'note' => $request->note,
            'status' => $request->status,
        ]);

        // Return a success response
        return response()->json(['message' => 'Committee created successfully', 200]);
    }
    /**
     * Display the specified resource.
     */
    public function show(CommitteeName $committeeName)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CommitteeName $committeeName)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CommitteeName $committeeName)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CommitteeName $committeeName)
    {
        //
    }
}
