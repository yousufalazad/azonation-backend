<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;



class SubscriptionController extends Controller
{

    // Validation method
    protected function validateRequest(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'package_id' => 'required|integer|exists:packages,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'boolean',
        ]);
    }
    // Fetch all subscriptions
    public function index()
    {
        //for SUPERADMIN 
        //return Subscription::all();
        try {
            // Fetch active packages
            $subscription = Subscription::where('status', true)->get();

            // Return JSON response with status and data
            return response()->json([
                'status' => true,
                'data' => $subscription,
            ]);
        } catch (\Exception $e) {
            // Log the exception for debugging
            Log::error('Error fetching packages: ' . $e->getMessage());

            // Return JSON response with error status
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching packages.',
            ], 500);
        }
    }

    // Store a new subscription
    public function store(Request $request)
    {
        $this->validateRequest($request);

        $subscription = Subscription::create($request->all());

        return response()->json($subscription, 201);
    }

    // Show a specific subscription
    public function show(Request $request)
    {
        //for USER
        try {
            $user_id = $request->user()->id; // Retrieve the authenticated user's ID
            $subscription = Subscription::where('user_id', $user_id)
            ->leftJoin('packages', 'subscriptions.package_id', 'packages.id')
            ->select('subscriptions.*', 'packages.name as package_name')
            ->get();
            // Return JSON response with status and data
            return response()->json([
                'status' => true,
                'data' => $subscription,
            ]);
        } catch (\Exception $e) {
            // Log the exception for debugging
            Log::error('Error fetching packages: ' . $e->getMessage());

            // Return JSON response with error status
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching packages.',
            ], 500);
        }
    }

    // Update an existing subscription
    public function update(Request $request, $id)
{
    $this->validateRequest($request);

    $subscription = Subscription::find($id);
    if (!$subscription) {
        return response()->json(['status' => false, 'message' => 'Subscription not found'], 404);
    }

    // Ensure start_date is today
    if ($request->start_date !== now()->toDateString()) {
        return response()->json(['status' => false, 'message' => 'Start date must be today'], 400);
    }

    // Update the subscription
    $subscription->update($request->all());
    return response()->json(['status' => true, 'message' => 'Subscription updated successfully', 'data' => $subscription]);
}

}
