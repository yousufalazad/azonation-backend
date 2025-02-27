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
        try {
            $records = EverydayMemberCountAndBilling::with('user')->get();

            return response()->json([
                'status' => true,
                'data' => $records,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve records.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Store a newly created resource
    public function superAdminStore(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'date' => 'required|date',
            // 'day_total_member' => 'required|integer',
            // 'day_total_bill' => 'required|numeric',
            // 'is_active' => 'required|boolean',
        ]);
        // $request['user_id'] = $request->user()->id;

        $record = EverydayMemberCountAndBilling::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Record created successfully',
            'data' => $record
        ], 201);
    }

    // Display the specified resource
    public function show($id)
    {
        try {
            $record = EverydayMemberCountAndBilling::with('user')->find($id);

            if (!$record) {
                return response()->json([
                    'status' => false,
                    'message' => 'Record not found',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $record,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // Update the specified resource
    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'sometimes|required|date',
            // 'day_total_member' => 'sometimes|required|integer',
            // 'day_total_bill' => 'sometimes|required|numeric',
            // 'is_active' => 'sometimes|required|boolean',
        ]);

        $record = EverydayMemberCountAndBilling::find($id);
        if (!$record) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        $record->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Record updated successfully',
            'data' => $record
        ], 201);
    }

    // Remove the specified resource
    public function destroy($id)
    {
        $record = EverydayMemberCountAndBilling::find($id);
        if (!$record) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        $record->delete();

        return response()->json([
            'status' => true,
            'message' => 'Deleted successfully.',
        ], 200);
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
            // Fetch the user
            $user = User::with(['userCountry.country.region', 'managementSubscription.managementPackage'])->findOrFail($userId);

            // Extract the region from the user's country
            $region = $user->userCountry->country->region->region;

            // Extract the user's subscribed package
            $managementPackage = $user->managementSubscription->managementPackage;

            // Fetch the price rate for the region and package
            $managementPriceRate = ManagementPricing::where('region_id', $region->id)
                ->where('management_package_id', $managementPackage->id)
                ->value('price_rate');

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
        Log::info('Everyday Management bill generation started.');
        try {
            $users = User::where('type', 'organisation')->get();

            Log::info('Everyday Management bill generation started for ' . $users->count() . ' users.');

            $singleUserData = $users->map(function ($user) {
                $userId = $user->id;
                $date = today();

                $getUserManagementDailyPriceRateResponse = $this->getUserManagementDailyPriceRate($userId);
                $getUserManagementDailyPriceRateData = $getUserManagementDailyPriceRateResponse->getData(true);
                Log::info('User management daily price rate fetched successfully.');

                // Calculate active members from org_member_lists
                $orgMembers = DB::table('org_members')
                    ->where('org_type_user_id', $userId)
                    ->where('is_active', true) // Only count active members
                    ->count();

                Log::info('org_members count reached');

                // Calculate active members from org_independent_members
                $independentMembers = DB::table('org_independent_members')
                    ->where('user_id', $userId)
                    ->where('is_active', true) // Only count active members
                    ->count();

                Log::info('org_independent_members count reached');

                // Total active members
                $totalMembers = $orgMembers + $independentMembers;

                Log::info('Total active members calculation successfully completed.');
                // Calculate the price rate per member
                $managementDailyPriceRate = 3; // Your price rate per member
                //$managementDailyPriceRate = $getUserManagementDailyPriceRateData['daily_price_rate']; // Your price rate per member

                Log::info('Daily price rate for ' . $userId . ' is 4' . $managementDailyPriceRate);

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
    public function X_show(EverydayMemberCountAndBilling $everydayMemberCountAndBilling)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function X_edit(EverydayMemberCountAndBilling $everydayMemberCountAndBilling)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function X_update(Request $request, EverydayMemberCountAndBilling $everydayMemberCountAndBilling)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function X_destroy(EverydayMemberCountAndBilling $everydayMemberCountAndBilling)
    {
        //
    }
}
