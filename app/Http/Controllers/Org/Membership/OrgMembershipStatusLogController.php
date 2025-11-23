<?php

namespace App\Http\Controllers\Org\Membership;

use App\Http\Controllers\Controller;
use App\Models\OrgMembershipStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrgMembershipStatusLogController extends Controller
{
    /**
     * Display a listing of status logs.
     */
    public function index()
    {
        try {
            $logs = OrgMembershipStatusLog::with(['orgUser', 'individualUser', 'membershipStatus'])
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    /**
     * Store a newly created log.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'org_type_user_id' => 'required|exists:users,id',
            'individual_type_user_id' => 'required|exists:users,id',
            'membership_status_id' => 'nullable|exists:membership_statuses,id',
            'membership_status_start' => 'nullable|date',
            'membership_status_end' => 'nullable|date',
            'membership_status_duration_days' => 'nullable|integer',
            'changed_at' => 'nullable|date',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $log = OrgMembershipStatusLog::create($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Membership status log created successfully.',
                'data' => $log
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    /**
     * Display a specific log.
     */
    public function show($id)
    {
        try {
            $log = OrgMembershipStatusLog::with(['orgUser', 'individualUser', 'membershipStatus'])
                ->find($id);

            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'Log not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $log
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    /**
     * Update a log.
     */
    public function update(Request $request, $id)
    {
        try {
            $log = OrgMembershipStatusLog::find($id);

            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'Log not found.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'org_type_user_id' => 'required|exists:users,id',
                'individual_type_user_id' => 'required|exists:users,id',
                'membership_status_id' => 'nullable|exists:membership_statuses,id',
                'membership_status_start' => 'nullable|date',
                'membership_status_end' => 'nullable|date',
                'membership_status_duration_days' => 'nullable|integer',
                'changed_at' => 'nullable|date',
                'reason' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $log->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Membership status log updated successfully.',
                'data' => $log
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove a log.
     */
    public function destroy($id)
    {
        try {
            $log = OrgMembershipStatusLog::find($id);

            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'Log not found.'
                ], 404);
            }

            $log->delete();

            return response()->json([
                'success' => true,
                'message' => 'Log deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
}
