<?php

namespace App\Http\Controllers\SuperAdmin\Financial\Management;

use App\Http\Controllers\Controller;

use App\Models\EverydayMemberCountAndBilling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use App\Models\ManagementPricing;

class EverydayMemberCountAndBillingController extends Controller
{
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
    public function superAdminStore(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);
        $record = EverydayMemberCountAndBilling::create($request->all());
        return response()->json([
            'status' => true,
            'message' => 'Record created successfully',
            'data' => $record
        ], 201);
    }
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
    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'sometimes|required|date',
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
    public function create() {}

    public function getUserManagementDailyPriceRate($userId)
    {
        try {
            $userId = Auth::id();
            $user = User::with(['userCountry.country.countryRegion.region', 'managementSubscription.managementPackage'])->findOrFail($userId);
            $region = $user->userCountry->country->countryRegion->region;
            $managementPackage = $user->managementSubscription->managementPackage;

            $managementPriceRate = ManagementPricing::where('region_id', $region->id)
                ->where('management_package_id', $managementPackage->id)
                ->value('price_rate');
            if ($managementPriceRate) {
                return response()->json([
                    'daily_price_rate' => $managementPriceRate,
                    'status' => true,
                    'message' => 'Daily price rate fetched successfully'
                ], 200);
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
                $orgMembers = DB::table('org_members')
                    ->where('org_type_user_id', $userId)
                    ->where('is_active', true)
                    ->count();
                Log::info('org_members count reached');
                $independentMembers = DB::table('org_independent_members')
                    ->where('user_id', $userId)
                    ->where('is_active', true)
                    ->count();
                Log::info('org_independent_members count reached');
                $totalMembers = $orgMembers + $independentMembers;
                Log::info('Total active members calculation successfully completed.');
                // $managementDailyPriceRate = 3;
                $managementDailyPriceRate = $getUserManagementDailyPriceRateData['daily_price_rate'];
                // Log::info('Daily price rate for ' . $userId . ' is 4' . $managementDailyPriceRate);
                $dayTotalBill = $totalMembers * $managementDailyPriceRate;
                Log::info('Day total member count and day bill calculation successfully completed.');
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

    public function currentMonthBillCalculation()
    {
        try {
            $userId = Auth::id();
            $startOfSubMonth = Carbon::now()->startOfMonth()->toDateString();
            $endOfSubMonth = Carbon::now()->endOfMonth()->toDateString();

            $monthlyTotalMemberCount = EverydayMemberCountAndBilling::where('user_id', $userId)
                ->whereBetween('date', [$startOfSubMonth, $endOfSubMonth])
                ->get();
            return response()->json([
                'status' => true,
                'data' => $monthlyTotalMemberCount,
                'message' => 'Monthly total member count fetched successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function subMonthBillCalculation()
    {
        try {
            $userId = Auth::id();
            $startOfSubMonth = Carbon::now()->subMonth()->startOfMonth()->toDateString();
            $endOfSubMonth = Carbon::now()->subMonth()->endOfMonth()->toDateString();

            $monthlyTotalMemberCount = EverydayMemberCountAndBilling::where('user_id', $userId)
                ->whereBetween('date', [$startOfSubMonth, $endOfSubMonth])
                ->get();
            return response()->json([
                'status' => true,
                'data' => $monthlyTotalMemberCount,
                'message' => 'Monthly total member count fetched successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function X_show(EverydayMemberCountAndBilling $everydayMemberCountAndBilling) {}
    public function X_edit(EverydayMemberCountAndBilling $everydayMemberCountAndBilling) {}
    public function X_update(Request $request, EverydayMemberCountAndBilling $everydayMemberCountAndBilling) {}
    public function X_destroy(EverydayMemberCountAndBilling $everydayMemberCountAndBilling) {}
}
