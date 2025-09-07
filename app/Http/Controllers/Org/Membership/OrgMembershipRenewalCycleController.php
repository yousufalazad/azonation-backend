<?php

namespace App\Http\Controllers\Org\Membership;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OrgMembershipRenewalCycle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrgMembershipRenewalCycleController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $cycles = OrgMembershipRenewalCycle::where('org_type_user_id', $userId)
            ->with(['memberRenewalCycle'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership renewal cycles retrieved successfully.',
            'data' => $cycles
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_renewal_cycle_id' => 'required|exists:membership_renewal_cycles,id',
            'alignment' => 'nullable|string',
            'anchor_month' => 'nullable|integer',
            'anchor_day' => 'nullable|integer',
            'anchor_weekday' => 'nullable|string',
            'use_last_day_of_month' => 'boolean',
            'timezone' => 'nullable|string',
            'proration_policy' => 'nullable|string',
            'grace_days' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'errors' => $validator->errors()
            ], 422);
        }

        $request['org_type_user_id'] = Auth::id();

        $cycle = OrgMembershipRenewalCycle::create($request->only([
            'org_type_user_id',
            'member_renewal_cycle_id',
            'alignment',
            'anchor_month',
            'anchor_day',
            'anchor_weekday',
            'use_last_day_of_month',
            'timezone',
            'proration_policy',
            'grace_days',
            'is_active'
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership renewal cycle created successfully.',
            'data' => $cycle
        ]);
    }

    public function show($id)
    {
        $cycle = OrgMembershipRenewalCycle::with('memberRenewalCycle')->find($id);

        if (!$cycle) {
            return response()->json([
                'status' => false,
                'message' => 'Organisation membership renewal cycle not found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership renewal cycle retrieved successfully.',
            'data' => $cycle
        ]);
    }

    public function update(Request $request, $id)
    {
        $cycle = OrgMembershipRenewalCycle::find($id);

        if (!$cycle) {
            return response()->json([
                'status' => false,
                'message' => 'Organisation membership renewal cycle not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'member_renewal_cycle_id' => 'required|exists:membership_renewal_cycles,id',
            'alignment' => 'nullable|string',
            'anchor_month' => 'nullable|integer',
            'anchor_day' => 'nullable|integer',
            'anchor_weekday' => 'nullable|string',
            'use_last_day_of_month' => 'boolean',
            'timezone' => 'nullable|string',
            'proration_policy' => 'nullable|string',
            'grace_days' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'errors' => $validator->errors()
            ], 422);
        }

        $cycle->update($request->only([
            'member_renewal_cycle_id',
            'alignment',
            'anchor_month',
            'anchor_day',
            'anchor_weekday',
            'use_last_day_of_month',
            'timezone',
            'proration_policy',
            'grace_days',
            'is_active'
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership renewal cycle updated successfully.',
            'data' => $cycle
        ]);
    }

    public function destroy($id)
    {
        $cycle = OrgMembershipRenewalCycle::find($id);

        if (!$cycle) {
            return response()->json([
                'status' => false,
                'message' => 'Organisation membership renewal cycle not found.'
            ], 404);
        }

        $cycle->delete();

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership renewal cycle deleted successfully.'
        ]);
    }
}