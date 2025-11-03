<?php

namespace App\Http\Controllers\Org\Membership;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\OrgMembershipRenewal;

class OrgMembershipRenewalController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $renewals = OrgMembershipRenewal::where('org_type_user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership renewals retrieved successfully.',
            'data' => $renewals
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'individual_type_user_id' => 'nullable|integer',
            'membership_renewal_cycle_id' => 'required|exists:membership_renewal_cycles,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'amount_paid' => 'nullable|numeric',
            'status' => 'required',
            'initiated_by' => 'nullable',
            'initiated_source' => 'nullable|string|max:100',
            'attempt_count' => 'nullable|integer',
            'last_attempt_at' => 'nullable|date',
            'renewed_at' => 'nullable|date',
            'invoice_id' => 'nullable|integer',
            'payment_id' => 'nullable|integer',
            'failure_code' => 'nullable|string|max:50',
            'failure_message' => 'nullable|string',
            'org_notes' => 'nullable|string',
            'idempotency_key' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'errors' => $validator->errors()
            ], 422);
        }

        $request['org_type_user_id'] = Auth::id();

        $renewal = OrgMembershipRenewal::create($request->only([
            'org_type_user_id',
            'individual_type_user_id',
            'membership_renewal_cycle_id',
            'period_start',
            'period_end',
            'amount_paid',
            'status',
            'initiated_by',
            'initiated_source',
            'attempt_count',
            'last_attempt_at',
            'renewed_at',
            'invoice_id',
            'payment_id',
            'failure_code',
            'failure_message',
            'org_notes',
            'idempotency_key',
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership renewal created successfully.',
            'data' => $renewal
        ]);
    }

    public function show($id)
    {
        $renewal = OrgMembershipRenewal::find($id);

        if (!$renewal) {
            return response()->json([
                'status' => false,
                'message' => 'Organisation membership renewal not found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership renewal retrieved successfully.',
            'data' => $renewal
        ]);
    }

    public function update(Request $request, $id)
    {
        $renewal = OrgMembershipRenewal::find($id);

        if (!$renewal) {
            return response()->json([
                'status' => false,
                'message' => 'Organisation membership renewal not found.'
            ], 404);
        }

        // dd($request->all());exit;
        $validator = Validator::make($request->all(), [
            'individual_type_user_id' => 'nullable|integer',
            'membership_renewal_cycle_id' => 'required|exists:membership_renewal_cycles,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'amount_paid' => 'nullable|numeric',
            'status' => 'required|string|max:50',
            'initiated_by' => 'nullable',
            'initiated_source' => 'nullable|string|max:100',
            'attempt_count' => 'nullable|integer',
            'last_attempt_at' => 'nullable|date',
            'renewed_at' => 'nullable|date',
            'invoice_id' => 'nullable|integer',
            'payment_id' => 'nullable|integer',
            'failure_code' => 'nullable|string|max:50',
            'failure_message' => 'nullable|string',
            'org_notes' => 'nullable|string',
            'idempotency_key' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'errors' => $validator->errors()
            ], 422);
        }

        $renewal->update($request->only([
            'individual_type_user_id',
            'membership_renewal_cycle_id',
            'period_start',
            'period_end',
            'amount_paid',
            'status',
            'initiated_by',
            'initiated_source',
            'attempt_count',
            'last_attempt_at',
            'renewed_at',
            'invoice_id',
            'payment_id',
            'failure_code',
            'failure_message',
            'org_notes',
            'idempotency_key',
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership renewal updated successfully.',
            'data' => $renewal
        ]);
    }

    public function destroy($id)
    {
        $renewal = OrgMembershipRenewal::find($id);

        if (!$renewal) {
            return response()->json([
                'status' => false,
                'message' => 'Organisation membership renewal not found.'
            ], 404);
        }

        $renewal->delete();

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership renewal deleted successfully.'
        ]);
    }
}