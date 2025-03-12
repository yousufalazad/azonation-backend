<?php
namespace App\Http\Controllers\SuperAdmin\Financial\Storage;
use App\Http\Controllers\Controller;

use App\Models\EverydayStorageBilling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\StoragePricing;
use Carbon\Carbon;

class EverydayStorageBillingController extends Controller
{
    public function index()
    {
        try {
            $records = EverydayStorageBilling::with('user')->get();
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
    public function create() {}
    public function getUserStorageDailyPriceRate($userId)
    {
        Log::info('getUserStorageDailyPriceRate started for 7' . $userId);
        $user = User::with(['country.region', 'storageSubscription.package'])->findOrFail($userId);
        Log::info('getUserStorageDailyPriceRate user data found 8' . $user->id);
        $subscription = $user->storageSubscription;
        if (!$subscription) {
            throw new \Exception("User does not have an active storage subscription.");
        }
        Log::info('getUserStorageDailyPriceRate subscription found 9' . $subscription->id);
        $region = $user->country->region;
        if (!$region) {
            throw new \Exception("Region not found for the user's country.");
        }
        Log::info('getUserStorageDailyPriceRate region found 10' . $region->region_id);
        $storagePriceRate = StoragePricing::where('region_id', $region->region_id)
            ->where('storage_package_id', $subscription->storage_package_id)
            ->value('price_rate');
        Log::info('Daily price rate for ' . $userId . ' is ' . $storagePriceRate);
        if ($storagePriceRate === null) {
            throw new \Exception("Price rate not found for the user's package in their region.");
        }
        return $storagePriceRate;
    }
    public function store(Request $request)
    {
        Log::info('Everyday storage bill generation started. 1');
        try {
            $users = User::where('type', 'organisation')->get();
            Log::info('Everyday storage bill generation started for ' . $users->count() . 'users. 2');
            $userData = $users->map(function ($user) {
                Log::info('Everyday storage bill generation started for 3 ' . $user->id);
                $userId = $user->id;
                $date = today();
                $storageDailyPriceRate = $this->getUserStorageDailyPriceRate($userId);;
                Log::info('Daily price rate for ' . $userId . ' is 4' . $storageDailyPriceRate);
                $dayTotalBill = 1 * $storageDailyPriceRate;
                Log::info('hmm. 5');
                DB::table('everyday_storage_billings')->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'date' => $date,
                    ],
                    [
                        'day_bill_amount' => $dayTotalBill,
                        'is_active' => true,
                    ]
                );
                Log::info('hmm 6');
            });
            return response()->json([
                'message' => 'Day storage bill calculation successfully recorded.',
                'status' => true,
                'data' => $userData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function superAdminStore(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);
        $record = EverydayStorageBilling::create($request->all());
        return response()->json([
            'status' => true,
            'message' => 'Record created successfully',
            'data' => $record
        ], 201);
    }
    public function show($id)
    {
        try {
            $record = EverydayStorageBilling::with('user')->find($id);
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
    public function edit(EverydayStorageBilling $everydayStorageBilling) {}
    public function update(Request $request,  $id)
    {
        $request->validate([
            'date' => 'sometimes|required|date',
        ]);
        $record = EverydayStorageBilling::find($id);
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
        $record = EverydayStorageBilling::find($id);
        if (!$record) {
            return response()->json(['message' => 'Record not found'], 404);
        }
        $record->delete();
        return response()->json([
            'status' => true,
            'message' => 'Deleted successfully.',
        ], 200);
    }
}
