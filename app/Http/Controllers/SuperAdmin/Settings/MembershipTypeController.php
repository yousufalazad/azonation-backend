<?php

namespace App\Http\Controllers\SuperAdmin\Settings;

use App\Http\Controllers\Controller;

use App\Models\MembershipType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MembershipTypeController extends Controller
{
    public function index()
    {
        $membershipType = MembershipType::all();
        return response()->json(['status' => true, 'data' => $membershipType], 200);
    }
    public function create() {}
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            Log::info('Meeting Conduct Type data: ', ['name' => $request->name, 'status' => $request->status]);
            $membershipType = MembershipType::create([
                'name' => $request->name,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $membershipType, 'message' => 'MembershipType created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating MembershipType: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create MembershipType.'], 500);
        }
    }
    public function show(MembershipType $membershipType) {}
    public function edit(MembershipType $membershipType) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $membershipType = MembershipType::find($id);
        if (!$membershipType) {
            return response()->json(['status' => false, 'message' => 'MembershipType not found.'], 404);
        }
        $membershipType->update([
            'name' => $request->name,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'data' => $membershipType, 'message' => 'MembershipType updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $membershipType = MembershipType::find($id);
        if (!$membershipType) {
            return response()->json(['status' => false, 'message' => 'MembershipType not found.'], 404);
        }
        $membershipType->delete();
        return response()->json(['status' => true, 'message' => 'MembershipType deleted successfully.'], 200);
    }
}
