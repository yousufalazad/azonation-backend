<?php

namespace App\Http\Controllers\Org\Membership;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\OrgMembershipRenewalPrice;

class OrgMembershipRenewalPriceController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $prices = OrgMembershipRenewalPrice::where('org_type_user_id', $userId)
            ->with(['orgMembershipType', 'orgMembershipRenewalCycle', 'memberRenewalCycle'])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership renewal prices retrieved successfully.',
            'data' => $prices
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'org_membership_type_id' => 'required|exists:org_membership_types,id',
            'org_mem_renewal_cycle_id' => 'nullable|exists:org_membership_renewal_cycles,id',
            // 'member_renewal_cycle_id' => 'nullable|exists:membership_renewal_cycles,id',
            'currency' => 'required|string|max:10',
            'unit_amount_minor' => 'required|integer',
            'is_recurring' => 'boolean',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'org_notes' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'errors' => $validator->errors()
            ], 422);
        }

        $request['org_type_user_id'] = Auth::id();

        $price = OrgMembershipRenewalPrice::create($request->only([
            'org_type_user_id',
            'org_membership_type_id',
            'org_mem_renewal_cycle_id',
            // 'member_renewal_cycle_id',
            'currency',
            'unit_amount_minor',
            'is_recurring',
            'valid_from',
            'valid_to',
            'org_notes',
            'is_active',
            'sort_order',
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership renewal price created successfully.',
            'data' => $price
        ]);
    }

    public function show($id)
    {
        $price = OrgMembershipRenewalPrice::with(['orgMembershipType', 'orgMembershipRenewalCycle', 'memberRenewalCycle'])
            ->find($id);

        if (!$price) {
            return response()->json([
                'status' => false,
                'message' => 'Organisation membership renewal price not found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership renewal price retrieved successfully.',
            'data' => $price
        ]);
    }

    public function update(Request $request, $id)
    {
        $price = OrgMembershipRenewalPrice::find($id);

        if (!$price) {
            return response()->json([
                'status' => false,
                'message' => 'Organisation membership renewal price not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'org_membership_type_id' => 'required|exists:org_membership_types,id',
            'org_mem_renewal_cycle_id' => 'nullable|exists:org_membership_renewal_cycles,id',
            // 'member_renewal_cycle_id' => 'nullable|exists:membership_renewal_cycles,id',
            'currency' => 'required|string|max:10',
            'unit_amount_minor' => 'required|integer',
            'is_recurring' => 'boolean',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'org_notes' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'errors' => $validator->errors()
            ], 422);
        }

        $price->update($request->only([
            'org_membership_type_id',
            'org_mem_renewal_cycle_id',
            // 'member_renewal_cycle_id',
            'currency',
            'unit_amount_minor',
            'is_recurring',
            'valid_from',
            'valid_to',
            'org_notes',
            'is_active',
            'sort_order',
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership renewal price updated successfully.',
            'data' => $price
        ]);
    }

    public function destroy($id)
    {
        $price = OrgMembershipRenewalPrice::find($id);

        if (!$price) {
            return response()->json([
                'status' => false,
                'message' => 'Organisation membership renewal price not found.'
            ], 404);
        }

        $price->delete();

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership renewal price deleted successfully.'
        ]);
    }
}