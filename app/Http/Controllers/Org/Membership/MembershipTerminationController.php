<?php

namespace App\Http\Controllers\Org\Membership;

use App\Http\Controllers\Controller;

use App\Models\MembershipTermination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\OrgMember;
use App\Models\MembershipTerminations;
use Exception;
class MembershipTerminationController extends Controller
{

    // public function getOrgTerminatedMembers(Request $request)
    // {
    //     $userId = Auth::id();
    //     $today = Carbon::today()->toDateString(); // get current date in YYYY-MM-DD format

    //     // $getOrgAllMembers = OrgMember::with(['individual', 'membershipType', 'memberProfileImage'])
    //     $getOrgAllMembers = MembershipTerminations ::with(['individual', 'membershipType', 'memberProfileImage'])
    //         ->where('org_type_user_id', $userId)
    //         ->where('is_active', '1')
    //         ->where(function ($query) use ($today) {
    //             $query->whereNotNull('membership_end_date') // must have an end date
    //                 ->where('membership_end_date', '<=', $today); // expired
    //         })
    //         ->get();

    //     $getOrgAllMembers = $getOrgAllMembers->map(function ($member) {
    //         $member->image_url = $member->memberProfileImage && $member->memberProfileImage->image_path
    //             ? url(Storage::url($member->memberProfileImage->image_path))
    //             : null;
    //         unset($member->memberProfileImage);
    //         return $member;
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'data' => $getOrgAllMembers
    //     ]);
    // }

   // Get all membership terminations
    public function index()
    {
        try {
            $terminations = MembershipTermination::all();
            return response()->json([
                'status' => true,
                'message' => 'Membership terminations retrieved successfully.',
                'data' => $terminations
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Store a new membership termination
    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'org_type_user_id' => 'required|integer',
        'individual_type_user_id' => 'required|integer',
        'terminated_member_name' => 'required|string|max:255',
        'terminated_member_email' => 'nullable|email|max:255',
        'terminated_member_mobile' => 'nullable|string|max:20',
        'terminated_at' => 'required|date',
        'processed_at' => 'nullable|date',
        'membership_termination_reason_id' => 'required|integer',
        'org_administrator_id' => 'required|integer',
        'rejoin_eligible' => 'required|boolean',
        'file_path' => 'nullable|file|mimes:pdf,doc,docx,xlsx,xls,ppt,pptx,jpg,jpeg,png|max:102400',
        'membership_duration_days' => 'nullable|integer',
        'membership_status_before_termination' => 'required|string|max:100',
        'org_note' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        $data = $request->all();

        if ($request->hasFile('file_path')) {
            $document = $request->file('file_path');
            $filePath = $document->storeAs(
                'org/membership_termination/files',
                now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                'public'
            );
            $data['file_path'] = $filePath; // Save to database
        }

        $termination = MembershipTermination::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Membership termination created successfully.',
            'data' => $termination
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'An error occurred. Please try again.',
            'error' => $e->getMessage()
        ], 500);
    }
}


    // Show a single membership termination
    public function show($id)
    {
        try {
            $termination = MembershipTermination::findOrFail($id);
            return response()->json([
                'status' => true,
                'message' => 'Membership termination retrieved successfully.',
                'data' => $termination
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    // Update a membership termination
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'org_type_user_id' => 'required|integer',
            'individual_type_user_id' => 'required|integer',
            'terminated_member_name' => 'required|string|max:255',
            'terminated_member_email' => 'nullable|email|max:255',
            'terminated_member_mobile' => 'nullable|string|max:20',
            'terminated_at' => 'required|date',
            'processed_at' => 'nullable|date',
            'membership_termination_reason_id' => 'required|integer',
            'org_administrator_id ' => 'required|integer',
            'rejoin_eligible' => 'required|boolean',
            'file_path' => 'nullable|string|max:255',
            'membership_duration_days' => 'nullable|integer',
            'membership_status_before_termination' => 'required|string|max:100',
            'org_note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $termination = MembershipTermination::findOrFail($id);
            $termination->update($request->all());
            return response()->json([
                'status' => true,
                'message' => 'Membership termination updated successfully.',
                'data' => $termination
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete a membership termination
    public function destroy($id)
    {
        try {
            $termination = MembershipTermination::findOrFail($id);
            $termination->delete();
            return response()->json([
                'status' => true,
                'message' => 'Membership termination deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}