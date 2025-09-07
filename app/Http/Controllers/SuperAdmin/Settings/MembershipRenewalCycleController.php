<?php

namespace App\Http\Controllers\SuperAdmin\Settings;

use App\Http\Controllers\Controller;
use App\Models\MembershipRenewalCycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MembershipRenewalCycleController extends Controller
{
    public function index()
    {
        $renewalCycles = MembershipRenewalCycle::all();
        return response()->json(['status' => true, 'data' => $renewalCycles], 200);
    }

    public function create() {}

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'duration_in_months' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            Log::info('Creating Membership Renewal Cycle: ', [
                'name' => $request->name,
                'duration_in_months' => $request->duration_in_months,
                'is_active' => $request->is_active,
            ]);

            $renewalCycle = MembershipRenewalCycle::create([
                'name' => $request->name,
                'duration_in_months' => $request->duration_in_months,
                'is_active' => $request->is_active,
            ]);

            return response()->json([
                'status' => true,
                'data' => $renewalCycle,
                'message' => 'Membership Renewal Cycle created successfully.'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Membership Renewal Cycle: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create Membership Renewal Cycle.'], 500);
        }
    }

    public function show(MembershipRenewalCycle $membershipRenewalCycle) {}

    public function edit(MembershipRenewalCycle $membershipRenewalCycle) {}

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'duration_in_months' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $renewalCycle = MembershipRenewalCycle::find($id);
        if (!$renewalCycle) {
            return response()->json(['status' => false, 'message' => 'Membership Renewal Cycle not found.'], 404);
        }

        $renewalCycle->update([
            'name' => $request->name,
            'duration_in_months' => $request->duration_in_months,
            'is_active' => $request->is_active,
        ]);

        return response()->json([
            'status' => true,
            'data' => $renewalCycle,
            'message' => 'Membership Renewal Cycle updated successfully.'
        ], 200);
    }

    public function destroy($id)
    {
        $renewalCycle = MembershipRenewalCycle::find($id);
        if (!$renewalCycle) {
            return response()->json(['status' => false, 'message' => 'Membership Renewal Cycle not found.'], 404);
        }

        $renewalCycle->delete();

        return response()->json([
            'status' => true,
            'message' => 'Membership Renewal Cycle deleted successfully.'
        ], 200);
    }
}
