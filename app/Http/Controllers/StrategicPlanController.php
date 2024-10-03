<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StrategicPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StrategicPlanController extends Controller
{
    // Get list of all strategic plans
    public function index()
    {
        try {
            $strategicPlans = StrategicPlan::with('user:id,name')->get();
            return response()->json([
                'status' => true,
                'data' => $strategicPlans
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch strategic plans.'
            ], 500);
        }
    }

    // Create a new strategic plan
    public function store(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'plan' => 'required|string|max:5000',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 400);
        }

        try {
            $strategicPlan = StrategicPlan::create([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'plan' => $request->plan,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                // image
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Strategic plan created successfully.',
                'data' => $strategicPlan
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create strategic plan.'
            ], 500);
        }
    }

    // Update an existing strategic plan
    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'plan' => 'required|string|max:5000',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 400);
        }

        try {
            $strategicPlan = StrategicPlan::findOrFail($id);

            $strategicPlan->update([
                'title' => $request->title,
                'plan' => $request->plan,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Strategic plan updated successfully.',
                'data' => $strategicPlan
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update strategic plan.'
            ], 500);
        }
    }

    // Delete a strategic plan
    public function destroy($id)
    {
        try {
            $strategicPlan = StrategicPlan::findOrFail($id);
            $strategicPlan->delete();

            return response()->json([
                'status' => true,
                'message' => 'Strategic plan deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete strategic plan.'
            ], 500);
        }
    }
}