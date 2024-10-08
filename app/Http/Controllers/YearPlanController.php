<?php

namespace App\Http\Controllers;

use App\Models\YearPlan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Include Log facade

class YearPlanController extends Controller
{
    /**
     * Display a listing of the year plans.
     */
    public function index()
    {
        try {
            $yearPlans = YearPlan::all(); // Fetch all year plans
            return response()->json(['status' => true, 'data' => $yearPlans], 200);
        } catch (\Exception $e) {
            Log::error('Year Plan Index Error: ' . $e->getMessage()); // Log error
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.'], 500);
        }
    }

    /**
     * Store a newly created year plan in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_year' => 'required|string|max:4',
            'end_year' => 'required|string|max:4',
            'goals' => 'nullable|string',
            'activities' => 'nullable|string',
            'budget' => 'required|numeric|min:0',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date',
            'privacy_setup_id' => 'required|integer|in:1,2,3',
            'published' => 'required|boolean',
            'status' => 'required|integer|in:1,2,3,4',
        ]);

        try {
            // Convert ISO date strings to 'Y-m-d' format
           // $validatedData['start_date'] = Carbon::parse($validatedData['start_date'])->format('Y-m-d');
            //$validatedData['end_date'] = Carbon::parse($validatedData['end_date'])->format('Y-m-d');

            $yearPlan = YearPlan::create($validatedData);
            return response()->json(['status' => true, 'message' => 'Year plan added successfully!', 'data' => $yearPlan], 201);
        } catch (\Exception $e) {
            Log::error('Year Plan Store Error: ' . $e->getMessage()); // Log error
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.'], 500);
        }
    }

    /**
     * Display the specified year plan.
     */
    public function show($id)
    {
        try {
            $yearPlan = YearPlan::findOrFail($id);
            return response()->json(['status' => true, 'data' => $yearPlan], 200);
        } catch (\Exception $e) {
            Log::error('Year Plan Show Error: ' . $e->getMessage()); // Log error
            return response()->json(['status' => false, 'message' => 'Year plan not found.'], 404);
        }
    }

    /**
     * Update the specified year plan in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_year' => 'required|string|max:4',
            'end_year' => 'required|string|max:4',
            'goals' => 'nullable|string',
            'activities' => 'nullable|string',
            'budget' => 'required|numeric|min:0',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date',
            'privacy_setup_id' => 'required|integer|in:1,2,3',
            'published' => 'required|boolean',
            'status' => 'required|integer|in:1,2,3,4',
        ]);

        try {
            $yearPlan = YearPlan::findOrFail($id);

            // Convert ISO date strings to 'Y-m-d' format
            //$validatedData['start_date'] = Carbon::parse($validatedData['start_date'])->format('Y-m-d');
            //$validatedData['end_date'] = Carbon::parse($validatedData['end_date'])->format('Y-m-d');

            $yearPlan->update($validatedData);
            return response()->json(['status' => true, 'message' => 'Year plan updated successfully!', 'data' => $yearPlan], 200);
        } catch (\Exception $e) {
            Log::error('Year Plan Update Error: ' . $e->getMessage()); // Log error
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.'], 500);
        }
    }

    /**
     * Remove the specified year plan from storage.
     */
    public function destroy($id)
    {
        try {
            $yearPlan = YearPlan::findOrFail($id);
            $yearPlan->delete();
            return response()->json(['status' => true, 'message' => 'Year plan deleted successfully!'], 200);
        } catch (\Exception $e) {
            Log::error('Year Plan Delete Error: ' . $e->getMessage()); // Log error
            return response()->json(['status' => false, 'message' => 'Year plan not found or cannot be deleted.'], 404);
        }
    }
}