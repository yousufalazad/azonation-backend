<?php

namespace App\Http\Controllers\Org;
use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Http\Request;

class MeetingController extends Controller
{


    public function index($user_id)
    {
        $meetingList = Meeting::where('user_id', $user_id)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $meetingList
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
            'name' => 'required|string',
        ]);

        // Create a new meeting record associated with the organisation
        Meeting::create([
            'user_id' => $request->user_id,
            'name' => $request->name,
            'short_name' => $request->short_name,
            'subject' => $request->subject,
            'date' => $request->date,
            'time' => $request->time,
            'description' => $request->description,
            'address' => $request->address,
            'agenda' => $request->agenda,
            'requirements' => $request->requirements,
            'note' => $request->note,
            'status' => $request->status,
            'conduct_type' => $request->conduct_type,
        ]);

        // Return a success response
        return response()->json(['message' => 'Meeting created successfully', 200]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Meeting $meeting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Meeting $meeting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Meeting $meeting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Meeting $meeting)
    {
        //
    }
}
