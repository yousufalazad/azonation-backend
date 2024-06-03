<?php

namespace App\Http\Controllers;

use App\Models\Individual;
use App\Models\OrgMemberList;
use Illuminate\Http\Request;

class IndividualController extends Controller
{

    public function search(Request $request)
    {
        $query = $request->input('query');

        $results = Individual::where('id', 'like', "%{$query}%")
            ->orWhere('user_id', 'like', "%{$query}%")
            ->orWhere('azon_id', 'like', "%{$query}%")
            ->orWhere('full_name', 'like', "%{$query}%")
            ->get();

        return response()->json([
            'status' => true,
            'data' => $results
        ]);
    }

    public function addMember(Request $request)
    {
        $validated = $request->validate([
            // 'org_id' => 'required',
            // 'individual_id' => 'required',
            'org_id' => 'required|exists:organisations,id',
            'individual_id' => 'required|exists:individuals,id',
        ]);

        OrgMemberList::create([
            'org_id' => $validated['org_id'],
            'individual_id' => $validated['individual_id'],
            'status' => 1
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Member added successfully'
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $individualData = Individual::where('user_id', $id)->first();
    
        if ($individualData) {
            return response()->json(['status' => true, 'data' => $individualData]);
        } else {
            return response()->json(['status' => false, 'message' => 'Individual data not found']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Individual $individual)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Individual $individual)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Individual $individual)
    {
        //
    }
}
