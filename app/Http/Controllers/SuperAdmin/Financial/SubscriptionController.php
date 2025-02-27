<?php
namespace App\Http\Controllers\SuperAdmin\Financial;
use App\Http\Controllers\Controller;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
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
    public function index()
    {
        try {
            $subscription = Subscription::where('status', true)->get();
            return response()->json([
                'status' => true,
                'data' => $subscription,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching packages: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching packages.',
            ], 500);
        }
    }
    public function store(Request $request)
    {
        $this->validateRequest($request);
        $subscription = Subscription::create($request->all());
        return response()->json($subscription, 201);
    }
    public function show(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $subscription = Subscription::where('user_id', $user_id)
                ->leftJoin('packages', 'subscriptions.package_id', 'packages.id')
                ->select('subscriptions.*', 'packages.name as package_name')
                ->get();
            return response()->json([
                'status' => true,
                'data' => $subscription,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching packages: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching packages.',
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $this->validateRequest($request);
        $subscription = Subscription::find($id);
        if (!$subscription) {
            return response()->json(['status' => false, 'message' => 'Subscription not found'], 404);
        }
        if ($request->start_date !== now()->toDateString()) {
            return response()->json(['status' => false, 'message' => 'Start date must be today'], 400);
        }
        $subscription->update($request->all());
        return response()->json(['status' => true, 'message' => 'Subscription updated successfully', 'data' => $subscription]);
    }
}
