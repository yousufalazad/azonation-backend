<?php

namespace App\Http\Controllers;

use App\Models\EverydayMemberCountAndBilling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use App\Models\ManagementPricing;


class EverydayMemberCountAndBillingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function getUserManagementDailyPriceRate($userId)
    {
        try {
            Log::info("price 1---" . $userId);
            // Fetch the user
            $user = User::with(['userCountry.country.region', 'managementSubscription.managementPackage'])->findOrFail($userId);

            Log::info("price 2---" . $userId);
            // Extract the region from the user's country
            $region = $user->userCountry->country->region->region;

            Log::info("price 3---" . $region);
            // Extract the user's subscribed package
            $managementPackage = $user->managementSubscription->managementPackage;

            Log::info("price 4---" . $managementPackage);

            // Fetch the price rate for the region and package
            $managementPriceRate = ManagementPricing::where('region_id', $region->id)
                ->where('management_package_id', $managementPackage->id)
                ->value('price_rate')
                ->get();

                Log::info('Price 5, rate fetched.'. $managementPriceRate);

            if ($managementPriceRate) {
                return response()->json([
                    'daily_price_rate' => $managementPriceRate,
                ]);
            } else {
                return response()->json([
                    'error' => 'Price rate not found for the user\'s region and package',
                ], 404);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching the daily price rate',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        Log::info('started.');
        try {
            $users = User::where('type', 'organisation')->get();

            Log::info('Everyday Management bill generation started for ' . $users->count() . ' users.');

            $singleUserData = $users->map(function ($user) {
                $userId = $user->id;
                $date = today();

                $getUserManagementDailyPriceRateResponse = $this->getUserManagementDailyPriceRate($userId);
                $getUserManagementDailyPriceRateData = $getUserManagementDailyPriceRateResponse->getData(true);
                Log::info('User management daily price rate fetched.');
                $managementDailyPriceRate = $getUserManagementDailyPriceRateData['price_rate'];
                Log::info('Daily price rate for '. $userId.'is '. $managementDailyPriceRate);

                // Calculate active members from org_member_lists
                $orgMembers = DB::table('org_members')
                    ->where('org_type_user_id', $userId)
                    ->where('is_active', true) // Only count active members
                    ->count();

                Log::info('org_members count ');

                // Calculate active members from org_independent_members
                $independentMembers = DB::table('org_independent_members')
                    ->where('user_id', $userId)
                    ->where('is_active', true) // Only count active members
                    ->count();

                Log::info('org_independent_members count');

                // Total active members
                $totalMembers = $orgMembers + $independentMembers;

                Log::info('Total members');

                // Calculate the price rate per member
                //$managementDailyPriceRate = 3; // Your price rate per member
                 // Your price rate per member

                Log::info('Daily price rate for '. $userId. ' is 4 -'. $managementDailyPriceRate);

                // Calculate the total bill amount based on the members and price rate
                $dayTotalBill = $totalMembers * $managementDailyPriceRate;
                
                Log::info('Day total member count and day bill calculation successfully completed.');

                // Insert the count into org_member_counts
                DB::table('everyday_member_count_and_billings')->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'date' => $date,
                    ],
                    [
                        'day_total_member' => $totalMembers,
                        'day_total_bill' => $dayTotalBill,
                        'is_active' => true,
                    ]
                );

            });
            Log::info('Day total member count and day bill calculation successfully recorded.');

            return response()->json([
                'message' => 'Day total member count and day bill calculation successfully recorded.',
                'status' => true,
                'data' => $singleUserData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EverydayMemberCountAndBilling $everydayMemberCountAndBilling)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EverydayMemberCountAndBilling $everydayMemberCountAndBilling)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EverydayMemberCountAndBilling $everydayMemberCountAndBilling)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EverydayMemberCountAndBilling $everydayMemberCountAndBilling)
    {
        //
    }
}
