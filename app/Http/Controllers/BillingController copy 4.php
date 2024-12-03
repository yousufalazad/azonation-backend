<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\ActiveMemberCount;
use App\Models\RegionalPricing;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class BillingController extends Controller
{
    public function totalActiveMember($userId)
    {

        // Define the start date as the first day of the month and end date as today
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();
        //$endDate = now();

        // Retrieve active member counts for the logged-in user from the start of the month until today
        $activeMemberCounts = ActiveMemberCount::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        return $activeMemberCounts->sum('active_member'); // Sum all active members for the user
    }

    public function regionalPrice($userId)
    {
        $regionalPrice = User::query()
            ->where('users.id', $userId)
            ->leftJoin('user_countries', 'users.id', '=', 'user_countries.user_id')
            ->leftJoin('country_regions', 'user_countries.country_id', '=', 'country_regions.country_id')
            ->leftJoin('regional_pricings', function ($join) {
                $join->on('regional_pricings.region_id', '=', 'country_regions.region_id')
                    ->on('regional_pricings.package_id', '=', 'subscriptions.package_id');
            })
            ->leftJoin('subscriptions', 'users.id', '=', 'subscriptions.user_id')
            ->select('regional_pricings.price as regional_price_rate')
            ->first();

        return $regionalPrice ? $regionalPrice->regional_price_rate : 0; // Return the price rate or 0 if not found
    }

    public function billAmountForAllUsers()
    {
        try {
            $users = User::where('type', 'organisation')->get(); // Fetch all organisation users

            $billingData = $users->map(function ($user) {
                $userId = $user->id;

                // Calculate total active members for the user
                $totalActiveMembers = $this->totalActiveMember($userId);

                // Fetch price rate for the user
                $regionalPriceRate = $this->regionalPrice($userId);

                // Calculate bill amount
                $billAmount = $totalActiveMembers * $regionalPriceRate;

                return [
                    'user_id' => $userId,
                    'user_name' => $user->name,
                    'total_active_members' => $totalActiveMembers,
                    'regional_price_rate' => $regionalPriceRate,
                    'bill_amount' => $billAmount,
                ];
            });

            return response()->json([
                'status' => true,
                'data' => $billingData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Get the authenticated user
            $user_id = $request->user()->id;

            // Fetch billing related to the authenticated user
            $billingList = Billing::where('user_id', $user_id)->get();

            // Return the billing data as a JSON response
            return response()->json([
                'status' => true,
                'data' => $billingList,
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            //'billing_code' => 'required',

            // 'user_id' => 'number|nullable',
            // 'user_name' => 'string|max:100|nullable',
            // 'description' => 'string|max:255|nullable',
            // 'billing_address' => 'string|max:255|nullable',
            // 'item_name' => 'string|max:255|nullable',
            // 'period_start' => 'date|nullable',
            // 'period_end' => 'date|nullable',
            // 'service_month' => 'string|max:9|nullable',
            // 'billing_month' => 'string|max:9|nullable',
            // 'total_active_member' => 'numeric|min:0|nullable',
            // 'total_billable_active_member' => 'numeric|min:0|nullable',
            // 'price_rate' => 'numeric|min:0|nullable',
            // 'bill_amount' => 'numeric|min:0|nullable',
            // 'status' => 'string|max:15|nullable',
            // 'admin_notes' => 'string|max:255|nullable',
            // 'is_active' => 'nullable',
        ]);

        // Create a new event record associated with the organisation
        Billing::create([
            // 'billing_code' => $request->billing_code,
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
            'description' => $request->description,
            'billing_address' => $request->billing_address,
            'item_name' => $request->item_name,
            'period_start' => $request->period_start,
            'period_end' => $request->period_end,
            'service_month' => $request->service_month,
            'billing_month' => $request->billing_month,
            'total_active_member' => $request->total_active_member,
            'total_billable_active_member' => $request->total_billable_active_member,
            'price_rate' => $request->price_rate,
            'bill_amount' => $request->bill_amount,
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'is_active' => $request->is_active,
        ]);

        // Return a success response
        return response()->json(['status' => true, 'message' => 'Billing created successfully'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show($billingId)
    {
        // Find the Project by ID
        $billing = Billing::find($billingId);

        // Check if Project exists
        if (!$billing) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }

        // Return the Project data
        return response()->json(['status' => true, 'data' => $billing], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Billing $billing)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validate the incoming request data
        $request->validate([
            //'billing_code' => 'required',
            'user_id' => 'number|nullable',
            'user_name' => 'string|max:100|nullable',
            'description' => 'string|max:255|nullable',
            'billing_address' => 'string|max:255|nullable',
            'item_name' => 'string|max:255|nullable',
            'period_start' => 'date|nullable',
            'period_end' => 'date|nullable',
            'service_month' => 'string|max:9|nullable',
            'billing_month' => 'string|max:9|nullable',
            'total_active_member' => 'numeric|min:0|nullable',
            'total_billable_active_member' => 'numeric|min:0|nullable',
            'price_rate' => 'numeric|min:0|nullable',
            'bill_amount' => 'numeric|min:0|nullable',
            'status' => 'string|max:15|nullable',
            'admin_notes' => 'string|max:255|nullable',
            'is_active' => 'nullable',
        ]);

        // Find the existing billing record by ID
        $billing = Billing::findOrFail($id);

        // Update the billing record with the new data
        $billing->update([
            //'billing_code' => $request->billing_code,
            'description' => $request->description,
            'billing_address' => $request->billing_address,
            'item_name' => $request->item_name,
            'period_start' => $request->period_start,
            'period_end' => $request->period_end,
            'service_month' => $request->service_month,
            'billing_month' => $request->billing_month,
            'total_active_member' => $request->total_active_member,
            'total_billable_active_member' => $request->total_billable_active_member,
            'price_rate' => $request->price_rate,
            'bill_amount' => $request->bill_amount,
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'is_active' => $request->is_active,
        ]);

        // Return a success response
        return response()->json(['status' => true, 'message' => 'Billing updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $billing = Billing::find($id);

        if (!$billing) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }

        $billing->delete();

        return response()->json(['status' => true, 'message' => 'Project deleted successfully']);
    }
}
