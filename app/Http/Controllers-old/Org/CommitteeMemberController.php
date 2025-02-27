<?php

namespace App\Http\Controllers\Org;
use App\Http\Controllers\Controller;
use App\Models\CommitteeMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
class CommitteeMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $committeeMember = CommitteeMember::select('committee_members.*','users.name as user_name', 'designations.name as designation_name')
        ->leftJoin('users', 'committee_members.user_id', '=', 'users.id')
        ->leftJoin('designations', 'committee_members.designation_id', '=', 'designations.id')
        ->get();
        return response()->json(['status' => true, 'data' => $committeeMember], 200);
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
            'committee_id' => 'required|integer',
            'user_id' => 'required|integer',
            'designation_id' => 'required|integer',
            'start_date' => 'nullable',
            'end_date' => 'nullable',
            'note' => 'nullable',
            'status' => 'required',
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            // Create a new CommitteeMember record
            $committeeMember = new CommitteeMember();
            $committeeMember->committee_id = $request->committee_id;
            $committeeMember->user_id = $request->user_id;
            $committeeMember->designation_id = $request->designation_id;
            $committeeMember->start_date = $request->start_date;
            $committeeMember->end_date = $request->end_date;
            $committeeMember->note = $request->note;
            $committeeMember->status = $request->status;
            // Save the record to the database
            $committeeMember->save();

            // Return success response
            return response()->json([
                'status' => true,
                'data' => $committeeMember,
                'message' => 'Meeting Minutes created successfully.'
            ], 201);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error creating Meeting Minutes: ' . $e->getMessage());

            // Return generic error response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $meetingMinute =  CommitteeMember::select('meeting_minutes.*', 'privacy_setups.id as privacy_id', 'privacy_setups.name as privacy_setup_name')
            ->leftJoin('privacy_setups', 'meeting_minutes.privacy_setup_id', '=', 'privacy_setups.id')
            ->where('meeting_minutes.id', $id)->first();

        // Check if meeting exists
        if (!$meetingMinute) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }

        // Return the meeting data
        return response()->json(['status' => true, 'data' => $meetingMinute], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CommitteeMember $committeeMember)
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
           'committee_id' => 'required|integer',
            'user_id' => 'required|integer',
            'designation_id' => 'required|integer',
            'start_date' => 'nullable',
            'end_date' => 'nullable',
            'note' => 'nullable',
            'status' => 'required',
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find the record by ID
            $committeeMember = CommitteeMember::findOrFail($id);            
            // Update the CommitteeMember record
            $committeeMember->committee_id = $request->committee_id;
            $committeeMember->user_id = $request->user_id;
            $committeeMember->designation_id = $request->designation_id;
            $committeeMember->start_date = $request->start_date;
            $committeeMember->end_date = $request->end_date;
            $committeeMember->note = $request->note;
            $committeeMember->status = $request->status;
            // Save the record to the database
            $committeeMember->save();

            // Return success response
            return response()->json([
                'status' => true,
                'data' => $committeeMember,
                'message' => 'Meeting Minutes updated successfully.'
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error updating Meeting Minutes: ' . $e->getMessage());

            // Return generic error response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Find the record by ID
            $committeeMember = CommitteeMember::findOrFail($id);

            // Delete the record from the database
            $committeeMember->delete();

            // Return success response
            return response()->json([
                'status' => true,
                'message' => 'Meeting Minutes deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error deleting Meeting Minutes: ' . $e->getMessage());

            // Return generic error response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
}
