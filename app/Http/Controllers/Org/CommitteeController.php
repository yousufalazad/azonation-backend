<?php

namespace App\Http\Controllers\Org;
use App\Http\Controllers\Controller;
use App\Models\Committee;
use Illuminate\Http\Request;

class CommitteeController extends Controller
{
    
    public function getCommitteeListByUserId($userId)
    {
        $committeeList = Committee::where('user_id', $userId)
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
            'name' => 'required|string',
        ]);

        // Create a new committee record associated with the organisation
        Committee::create([
            'user_id' => $request->user_id,
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
    public function show(Committee $committee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Committee $committee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string',
            'short_description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'note' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        // Find the committee record associated with the user
        $committee = Committee::where('id', $id)->first();

        if (!$committee) {
            return response()->json(['message' => 'Committee not found'], 404);
        }

        // Update the committee record
        $committee->update([
            'name' => $validatedData['name'],
            'short_description' => $validatedData['short_description'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'note' => $validatedData['note'],
            'status' => $validatedData['status'],
        ]);

        // Return a success response
        return response()->json(['message' => 'Committee updated successfully'], 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Committee $committee)
    {
        //
    }
}
