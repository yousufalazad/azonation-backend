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
            $user = User::with(['userCountry.country.countryRegion.region', 'managementSubscription.managementPackage'])->findOrFail($userId);
            $managementPackageData = $user->managementSubscription->managementPackage;            
            $regionData = $user->userCountry->country->countryRegion->region;

            $managementPriceRate = ManagementPricing::where('region_id', $regionData->id)
                ->where('management_package_id', $managementPackageData->id)
                ->value('price_rate');
            return $managementPriceRate;

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching the daily price rate',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $users = User::where('type', 'organisation')->get();

            $singleUserData = $users->map(function ($user) {
                $userId = $user->id;
                $date = today();

                // Calculate active members from org_members
                $orgMembers = DB::table('org_members')
                    ->where('org_type_user_id', $userId)
                    ->where('is_active', true) // Only count active members
                    ->count();

                Log::info('Total member connected member: ' . $orgMembers);

                // Calculate active members from org_independent_members
                $independentMembers = DB::table('org_independent_members')
                    ->where('user_id', $userId)
                    ->where('is_active', true) // Only count active members
                    ->count();

                // Total members
                $totalMembers = $orgMembers + $independentMembers;

                //Get management daily price rate from other function
                $managementDailyPriceRate = $this->getUserManagementDailyPriceRate($userId);

                $dayTotalBill = $totalMembers * $managementDailyPriceRate;

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