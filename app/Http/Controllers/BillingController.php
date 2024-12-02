<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\ActiveMemberCount;
use App\Models\RegionalPricing;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;


class BillingController extends Controller
{
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

    // 'user_id',
    //     'user_name',
    //     'billing_address',
    //     'item_name',
    //     'period_start',
    //     'period_end',
    //     'active_member_count',
    //     'billable_active_member_count',
    //     'member_daily_rate',
    //     'total_bill_amount',
    //     'status',
    //     'admin_notes',
    //     'is_active'

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function totalActiveMember(Request $request)
    {
        // Get the authenticated user
        $user_id = $request->user()->id;

        // Define the start date as the first day of the month and end date as today
        $startDate = now()->startOfMonth();
        $endDate = now();

        // Retrieve active member counts for the logged-in user from the start of the month until today
        $activeMemberCounts = ActiveMemberCount::where('user_id', $user_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // Create a full list of dates from the start of the month to today
        $dateRange = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lessThanOrEqualTo($endDate)) {
            $dateRange[] = $currentDate->copy()->toDateString();
            $currentDate->addDay();
        }

        // Combine the counts with the date range
        $fullCounts = [];
        $totalActiveMembers = 0; // Initialize total active members count
        $priceRate = 0.03; // Define the price rate
        $approximateBill = 0; // Initialize approximate bill amount

        foreach ($dateRange as $date) {
            $memberCount = $activeMemberCounts->firstWhere('date', $date);
            $activeMemberCount = $memberCount ? $memberCount->active_member : 0;
            $isBillable = $memberCount ? $memberCount->is_billable : false;

            // Add to total active members
            $totalActiveMembers += $activeMemberCount;

            $fullCounts[] = [
                'date' => $date,
                'active_member' => $activeMemberCount,
                'is_billable' => $isBillable,
            ];
        }

        // Calculate the approximate bill amount until today
        $approximateBill = $totalActiveMembers * $priceRate;

        // Return a JSON response with the total active members and approximate bill
        // return response()->json([
        //     'status' => true,
        //     'data' => $fullCounts,
        //     'total_active_members' => $totalActiveMembers, // Include the total in the response
        //     'approximate_bill' => $approximateBill, // Include the approximate bill
        //     'price_rate' => $priceRate, // Include the price rate
        // ]);
    }

    public function regionalPriceRate(Request $request){
        try {
            // Fetch all users of type 'organisation' and their price rates
            $packageRegionCurrency = User::query()
                ->where('users.type', 'organisation') // Filter users by type
                ->leftJoin('user_countries', 'users.id', '=', 'user_countries.user_id') // Join country_regions table
                ->leftJoin('country_regions', 'user_countries.country_id', '=', 'country_regions.country_id')
                ->leftJoin('region_currencies', 'country_regions.region_id', '=', 'region_currencies.region_id')
                ->leftJoin('currencies', 'region_currencies.currency_id', '=', 'currencies.id')
                ->leftJoin('subscriptions', 'users.id', '=', 'subscriptions.user_id') //
                ->leftJoin('packages', 'subscriptions.package_id', '=', 'packages.id')
                ->leftJoin('regional_pricings', function ($join) {
                    $join->on('subscriptions.package_id', '=', 'regional_pricings.package_id')
                        ->on('country_regions.region_id', '=', 'regional_pricings.region_id');
                })
                ->select(
                    'users.id as user_id', 
                    'users.name as user_name',
                    'subscriptions.package_id',
                    'packages.name as package_name',
                    'subscriptions.start_date as subscription_start_date',
                    'country_regions.region_id',
                    'currencies.currency_code',
                    'regional_pricings.price'
                )
                ->get();

            // Check if the result is empty
            if ($packageRegionCurrency->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No price rates found for organisation users.'
                ], 404);
            }

            // Transform data for a better response format
            $result = $packageRegionCurrency->map(function ($record) {
                return [
                    'user_id' => $record->user_id,
                    'user_name' => $record->user_name,
                    'package_id' => $record->package_id,
                    'package_name' => $record->package_name,
                    'subscription_start_date' => $record->subscription_start_date,
                    'region_id' => $record->region_id,
                    'currency_code' => $record->currency_code,
                    'price' => $record->price,
                ];
            });

            return response()->json([
                'status' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error fetching organisation users price rates: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return error details in the response
            return response()->json([
                'status' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            ], 500);
        }
    }

    public function billAmount(Request $request, $id){

    }

    public function store(Request $request)
    {
        
        try {
            // Get the authenticated user
          $user_id = $request->user()->id;
          $startDate = now()->startOfMonth();
        //$endDate = now();
        $endDate = now()->endOfMonth();
 
          // Fetch billing related to the authenticated user
          //$totalBillableActiveMember = ActiveMemberCount::where('user_id', $user_id)->select('active_member_counts.active_member as daily_active_member')->get();
          $activeMemberCounts = ActiveMemberCount::where('user_id', $user_id)
          ->whereBetween('date', [$startDate, $endDate])
          ->get();

        // $regionalPriceRate =
        // $regionalCurrency =
        // $periodStart =
        // $periodEnd =
        // $serviceMonth = 
        // $billingMonth =
        // $serviceYear = 
        // $billingYear =
 
        // $billAmount = 0;
          // Return the billing data as a JSON response
        //   return response()->json([
        //       'status' => true,
        //       'data' => $billingList,
        //   ]);
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

    // $activeMemberCounts = ActiveMemberCount::where('user_id', $user_id)
    //     ->whereBetween('date', [$startDate, $endDate])
    //     ->get();

    // // Create a full list of dates from the start of the month to today
    // $dateRange = [];
    // $currentDate = $startDate->copy();

    // while ($currentDate->lessThanOrEqualTo($endDate)) {
    //     $dateRange[] = $currentDate->copy()->toDateString();
    //     $currentDate->addDay();
    // }

    // // Combine the counts with the date range
    // $fullCounts = [];
    // $totalActiveMembers = 0; // Initialize total active members count
    // $priceRate = 0.03; // Define the price rate
    // $approximateBill = 0; // Initialize approximate bill amount

    // foreach ($dateRange as $date) {
    //     $memberCount = $activeMemberCounts->firstWhere('date', $date);
    //     $activeMemberCount = $memberCount ? $memberCount->active_member : 0;
    //     $isBillable = $memberCount ? $memberCount->is_billable : false;

    //     // Add to total active members
    //     $totalActiveMembers += $activeMemberCount;

    //     $fullCounts[] = [
    //         'date' => $date,
    //         'active_member' => $activeMemberCount,
    //         'is_billable' => $isBillable,
    //     ];
    // }

    // // Calculate the approximate bill amount until today
    // $approximateBill = $totalActiveMembers * $priceRate;

    // // Return a JSON response with the total active members and approximate bill
    // return response()->json([
    //     'status' => true,
    //     'data' => $fullCounts,
    //     'total_active_members' => $totalActiveMembers, // Include the total in the response
    //     'approximate_bill' => $approximateBill, // Include the approximate bill
    //     'price_rate' => $priceRate, // Include the price rate
    // ]);

    /**
     * Display the specified resource.
     */
    public function show(Billing $billing)
    {
        //
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
    public function update(Request $request, Billing $billing)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Billing $billing)
    {
        //
    }
}
