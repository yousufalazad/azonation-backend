<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountFund;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AccountFundController extends Controller
{

    // Get all funds
    public function index()
    {
        $funds = AccountFund::all();
        return response()->json(['status' => true, 'data' => $funds], 200);
    }

    // Store a new fund
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Retrieve authenticated user ID
            $user_id = $request->user()->id;

            // Logging the inputs for debugging
            Log::info('Creating fund for user ID: ' . $user_id);
            Log::info('Fund data: ', ['name' => $request->name, 'status' => $request->status]);

            // Create the fund record
            $fund = AccountFund::create([
                'user_id' => $user_id,
                'name' => $request->name,
                'status' => $request->status,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $fund, 'message' => 'Fund created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating fund: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create fund.'], 500);
        }
    }

    // Update an existing fund
    public function update(Request $request, $id)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        // Find the fund
        $fund = AccountFund::find($id);
        if (!$fund) {
            return response()->json(['status' => false, 'message' => 'Fund not found.'], 404);
        }

        // Update the fund
        $fund->update([
            'name' => $request->name,
            'status' => $request->status,
        ]);

        return response()->json(['status' => true, 'data' => $fund, 'message' => 'Fund updated successfully.'], 200);
    }

    // Delete a fund
    public function destroy($id)
    {
        $fund = AccountFund::find($id);
        if (!$fund) {
            return response()->json(['status' => false, 'message' => 'Fund not found.'], 404);
        }

        $fund->delete();
        return response()->json(['status' => true, 'message' => 'Fund deleted successfully.'], 200);
    }
}
