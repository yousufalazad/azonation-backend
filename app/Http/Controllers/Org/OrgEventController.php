<?php

namespace App\Http\Controllers\Org;
use App\Http\Controllers\Controller;
use App\Models\OrgEvent;
use Illuminate\Http\Request;

class OrgEventController extends Controller
{
    public function index($orgId)
    {
        $eventList = OrgEvent::where('org_id', $orgId)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $eventList
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
        OrgEvent::create([
            'org_id' => $request->orgId,
            'title' => $request->title,
            'name' => $request->name,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'date' => $request->date,
            'time' => $request->time,
            'venue_name' => $request->venue_name,
            'venue_address' => $request->venue_address,
            'requirements' => $request->requirements,
            'note' => $request->note,
            'status' => $request->status,
            'conduct_type' => $request->conduct_type,
        ]);

        // Return a success response
        return response()->json(['message' => 'Event created successfully', 200]);
    }

    /**
     * Display the specified resource.
     */
    public function show(OrgEvent $orgEvent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrgEvent $orgEvent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrgEvent $orgEvent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrgEvent $orgEvent)
    {
        //
    }
}
