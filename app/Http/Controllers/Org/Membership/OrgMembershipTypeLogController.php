<?php

namespace App\Http\Controllers\Org\Membership;

use App\Http\Controllers\Controller;
use App\Models\OrgMembershipTypeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrgMembershipTypeLogController extends Controller
{
    /**
     * Display a listing of membership type logs.
     */
    public function index()
    {
        try {
            $logs = OrgMembershipTypeLog::with(['orgUser', 'individualUser', 'membershipType'])
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
     * Store a newly created log entry.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'org_type_user_id' => 'required|exists:users,id',
            'individual_type_user_id' => 'required|exists:users,id',
            'membership_type_id' => 'nullable|exists:membership_types,id',
            'membership_type_start' => 'nullable|date',
            'membership_type_end' => 'nullable|date',
            'membership_type_duration_days' => 'nullable|integer',
            'changed_at' => 'nullable|date',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $log = OrgMembershipTypeLog::create($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Membership type log created successfully.',
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
            $log = OrgMembershipTypeLog::with(['orgUser', 'individualUser', 'membershipType'])
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
     * Update an existing log.
     */
    public function update(Request $request, $id)
    {
        try {
            $log = OrgMembershipTypeLog::find($id);

            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'Log not found.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'org_type_user_id' => 'required|exists:users,id',
                'individual_type_user_id' => 'required|exists:users,id',
                'membership_type_id' => 'nullable|exists:membership_types,id',
                'membership_type_start' => 'nullable|date',
                'membership_type_end' => 'nullable|date',
                'membership_type_duration_days' => 'nullable|integer',
                'changed_at' => 'nullable|date',
                'reason' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $log->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Membership type log updated successfully.',
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
     * Delete a membership type log.
     */
    public function destroy($id)
    {
        try {
            $log = OrgMembershipTypeLog::find($id);

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
