<?php

namespace App\Http\Controllers;

use App\Models\OrgMemberList;
use App\Models\Organisation;
use App\Models\Individual;
use Illuminate\Http\Request;

class OrgMemberListController extends Controller
{
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $request->validate([
        //     'org_id' => 'required|integer',
        //     'org_id' => 'required|integer',
        // ]);

        // Create a new individual record associated with the user
        
        
        OrgMemberList::create([
            'org_id' => $request->org_id,
            'individual_id' => $request->individual_id,
            //'status' => $request->status,
            
        ]);

        // Return a success response
        //return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(OrgMemberList $orgMemberList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrgMemberList $orgMemberList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrgMemberList $orgMemberList)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrgMemberList $orgMemberList)
    {
        //
    }
}
