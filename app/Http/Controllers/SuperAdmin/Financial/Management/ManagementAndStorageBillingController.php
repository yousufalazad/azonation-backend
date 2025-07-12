<?php
namespace App\Http\Controllers\SuperAdmin\Financial\Management;
use App\Http\Controllers\Controller;

use App\Models\ManagementAndStorageBilling;
use Illuminate\Http\Request;
use App\Models\EverydayMemberCountAndBilling;
use App\Models\EverydayStorageBilling;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ManagementAndStorageBillingController extends Controller
{
    public function orgAllBill(Request $request)
    {
        try {
            $userId = Auth::id();
            $orgAllBill = ManagementAndStorageBilling::where('user_id', $userId)->get();
            return response()->json([
                'status' => true,
                'data' => $orgAllBill,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching packages: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching packages.',
            ], 500);
        }
    }
    public function index(Request $request)
    {
        try {
            $billingList = ManagementAndStorageBilling::all();
            return response()->json([
                'status' => true,
                'data' => $billingList,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching packages: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching packages.',
            ], 500);
        }
    }
    public function create() {}
    public function getUserCurrency($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $userCurrency = $user->userCountry
                ->country
                ->region
                ->regionCurrency
                ->currency;
            if ($userCurrency) {
                return response()->json([
                    'currency_code' => $userCurrency->currency_code,
                ]);
            }
            return response()->json([
                'error' => 'Currency not found for the user',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while retrieving the user currency.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    public function billCalculation($userId)
    {
        $startOfPreviousMonth = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $endOfPreviousMonth = Carbon::now()->subMonth()->endOfMonth()->toDateString();
        $monthlyTotalMemberCount = EverydayMemberCountAndBilling::where('user_id', $userId)
            ->whereBetween('date', [$startOfPreviousMonth, $endOfPreviousMonth])
            ->sum('day_total_member');
        $monthlyTotalManagementBillAmount = EverydayMemberCountAndBilling::where('user_id', $userId)
            ->whereBetween('date', [$startOfPreviousMonth, $endOfPreviousMonth])
            ->sum('day_total_bill');
        $monthlyTotalStorageBillAmount = EverydayStorageBilling::where('user_id', $userId)
            ->whereBetween('date', [$startOfPreviousMonth, $endOfPreviousMonth])
            ->sum('day_bill_amount');
        return response()->json([
            'status' => true,
            'total_member' => $monthlyTotalMemberCount,
            'total_management_bill_amount' => $monthlyTotalManagementBillAmount,
            'total_storage_bill_amount' => $monthlyTotalStorageBillAmount,
        ]);
    }
    public function store(Request $request)
    {
        Log::info('Store function is being executed');
        try {
            $users = User::where('type', 'organisation')->get();
            Log::info('Fetched users:', ['users' => $users->toArray()]);
            $userBill = $users->map(function ($user) {
                $userId = $user->id;
                $org_name = $user->first_name . ' ' . $user->last_name;
                $billCalculationResponse = $this->billCalculation($userId);
                $billCalculationData = $billCalculationResponse->getData(true);
                $userCurrencyResponse = $this->getUserCurrency($userId);
                $userCurrencyData = $userCurrencyResponse->getData(true);
                Log::info('Bill Calculation Data:', ['userId' => $userId, 'data' => $billCalculationData]);
                Log::info('User Currency Data:', ['userId' => $userId, 'data' => $userCurrencyData]);
                $startOfPreviousMonth = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                $endOfPreviousMonth = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                try {
                    ManagementAndStorageBilling::create([
                        'user_id' => $userId,
                        'org_name' => $org_name,
                        'service_month' => Carbon::now()->subMonth()->format('F'),
                        'service_year' => Carbon::now()->subMonth()->format('Y'),
                        'billing_month' => Carbon::now()->format('F'),
                        'billing_year' => Carbon::now()->format('Y'),
                        'period_start' => $startOfPreviousMonth,
                        'period_end' => $endOfPreviousMonth,
                        'total_member' => $billCalculationData['total_member'],
                        'total_management_bill_amount' => $billCalculationData['total_management_bill_amount'],
                        'total_storage_bill_amount' => $billCalculationData['total_storage_bill_amount'],
                        'currency_code' => $userCurrencyData['currency_code'],
                        'bill_status' => 'issued',
                        'admin_note' => 'non-refundable',
                        'is_active' => 1,
                    ]);
                    Log::info("Management billing created for user $userId");
                } catch (\Exception $e) {
                    Log::error("Error creating management billing for user $userId: " . $e->getMessage());
                }
            });
            return response()->json([
                'status' => true,
                'data' => $userBill,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function storeBySystem(Request $request)
    {
        $request->validate([]);
        ManagementAndStorageBilling::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
            'period_start' => $request->period_start,
            'period_end' => $request->period_end,
            'service_month' => $request->service_month,
            'service_year' => $request->service_year,
            'billing_month' => $request->billing_month,
            'billing_year' => $request->billing_year,
            'total_member' => $request->total_member,
            'total_management_bill_amount' => $request->total_management_bill_amount,
            'total_storage_bill_amount' => $request->total_storage_bill_amount,
            'currency_code' => $request->currency_code,
            'bill_status' => $request->bill_status,
            'admin_notes' => $request->admin_notes,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'message' => 'ManagementAndStorageBilling created successfully'], 200);
    }
    public function indexSuperAdmin(Request $request)
    {
        try {
            $billingList = ManagementAndStorageBilling::get();
            return response()->json([
                'status' => true,
                'data' => $billingList,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching packages: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching packages.',
            ], 500);
        }
    }
    public function show($billingId)
    {
        $billing = ManagementAndStorageBilling::find($billingId);
        if (!$billing) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $billing], 200);
    }
    public function edit(ManagementAndStorageBilling $billing) {}
    public function update(Request $request, $id)
    {
        $request->validate([]);
        $billing = ManagementAndStorageBilling::findOrFail($id);
        $billing->update([
            'period_start' => $request->period_start,
            'period_end' => $request->period_end,
            'service_month' => $request->service_month,
            'service_year' => $request->service_year,
            'billing_month' => $request->billing_month,
            'billing_year' => $request->billing_year,
            'total_member' => $request->total_member,
            'total_management_bill_amount' => $request->total_management_bill_amount,
            'total_storage_bill_amount' => $request->total_storage_bill_amount,
            'bill_amount' => $request->bill_amount,
            'bill_status' => $request->bill_status,
            'admin_notes' => $request->admin_notes,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'message' => 'Billing updated successfully'], 200);
    }
    public function destroy($id)
    {
        $billing = ManagementAndStorageBilling::find($id);
        if (!$billing) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }
        $billing->delete();
        return response()->json(['status' => true, 'message' => 'Project deleted successfully']);
    }
}
