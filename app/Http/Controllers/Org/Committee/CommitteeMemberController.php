<?php
namespace App\Http\Controllers\Org\Committee;
use App\Http\Controllers\Controller;

use App\Models\CommitteeMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CommitteeMemberController extends Controller
{
    public function index()
    {
        $committeeMember = CommitteeMember::select('committee_members.*', 'users.name as user_name', 'designations.name as designation_name')
            ->leftJoin('users', 'committee_members.user_id', '=', 'users.id')
            ->leftJoin('designations', 'committee_members.designation_id', '=', 'designations.id')
            ->get();
        return response()->json(['status' => true, 'data' => $committeeMember], 200);
    }
    public function create() {}
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'committee_id' => 'required|integer',
            'user_id' => 'required|integer',
            'designation_id' => 'required|integer',
            'start_date' => 'nullable',
            'end_date' => 'nullable',
            'note' => 'nullable',
            'status' => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            $committeeMember = new CommitteeMember();
            $committeeMember->committee_id = $request->committee_id;
            $committeeMember->user_id = $request->user_id;
            $committeeMember->designation_id = $request->designation_id;
            $committeeMember->start_date = $request->start_date;
            $committeeMember->end_date = $request->end_date;
            $committeeMember->note = $request->note;
            $committeeMember->status = $request->status;
            $committeeMember->save();
            return response()->json([
                'status' => true,
                'data' => $committeeMember,
                'message' => 'Meeting Minutes created successfully.'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Meeting Minutes: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
    public function show($id)
    {
        $meetingMinute =  CommitteeMember::select('meeting_minutes.*', 'privacy_setups.id as privacy_id', 'privacy_setups.name as privacy_setup_name')
            ->leftJoin('privacy_setups', 'meeting_minutes.privacy_setup_id', '=', 'privacy_setups.id')
            ->where('meeting_minutes.id', $id)->first();
        if (!$meetingMinute) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $meetingMinute], 200);
    }
    public function edit(CommitteeMember $committeeMember) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'committee_id' => 'required|integer',
            'user_id' => 'required|integer',
            'designation_id' => 'required|integer',
            'start_date' => 'nullable',
            'end_date' => 'nullable',
            'note' => 'nullable',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            $committeeMember = CommitteeMember::findOrFail($id);
            $committeeMember->committee_id = $request->committee_id;
            $committeeMember->user_id = $request->user_id;
            $committeeMember->designation_id = $request->designation_id;
            $committeeMember->start_date = $request->start_date;
            $committeeMember->end_date = $request->end_date;
            $committeeMember->note = $request->note;
            $committeeMember->status = $request->status;
            $committeeMember->save();
            return response()->json([
                'status' => true,
                'data' => $committeeMember,
                'message' => 'Meeting Minutes updated successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating Meeting Minutes: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $committeeMember = CommitteeMember::findOrFail($id);
            $committeeMember->delete();
            return response()->json([
                'status' => true,
                'message' => 'Meeting Minutes deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting Meeting Minutes: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
}
