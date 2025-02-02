<?php

namespace App\Http\Controllers;

use App\Models\ManagementAndStorageBilling;
use Illuminate\Http\Request;
use App\Models\EverydayMemberCountAndBilling;
use App\Models\EverydayStorageBilling;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ManagementAndStorageBillingController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Get the authenticated user
            $user_id = $request->user()->id;
            // Fetch billing related to the authenticated user
            $billingList = ManagementAndStorageBilling::where('user_id', $user_id)->get();
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

    public function getUserCurrency($userId)
    {
        try {
            // Fetch user by ID
            $user = User::findOrFail($userId);

            // Traverse relationships to find the currency
            $userCurrency = $user->userCountry // Fetch user's country
                ->country // Access the associated country
                ->region // Access the associated region
                ->regionCurrency // Fetch region's currency
                ->currency; // Get the actual currency

            // If currency exists, return the currency details
            if ($userCurrency) {
                return response()->json([
                    'currency_code' => $userCurrency->currency_code,
                ]);
            }

            // If no currency is found, return a 404 response
            return response()->json([
                'error' => 'Currency not found for the user',
            ], 404);
        } catch (\Exception $e) {
            // Handle errors gracefully
            return response()->json([
                'error' => 'An error occurred while retrieving the user currency.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }


    public function billCalculation($userId)
    {
        // Get the start and end dates of the previous month
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


            // Map through the users and calculate the bill for each user
            $userBill = $users->map(function ($user) {
                $userId = $user->id;
                $userName = $user->name;

                $billCalculationResponse = $this->billCalculation($userId);
                $billCalculationData = $billCalculationResponse->getData(true);

                $userCurrencyResponse = $this->getUserCurrency($userId);
                $userCurrencyData = $userCurrencyResponse->getData(true);

                Log::info('Bill Calculation Data:', ['userId' => $userId, 'data' => $billCalculationData]);
                Log::info('User Currency Data:', ['userId' => $userId, 'data' => $userCurrencyData]);


                // Get the start and end dates of the previous month
                $startOfPreviousMonth = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                $endOfPreviousMonth = Carbon::now()->subMonth()->endOfMonth()->toDateString();

                // Create a new management billing record
                try {
                    ManagementAndStorageBilling::create([
                        'user_id' => $userId,
                        'user_name' => $userName,
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

    public function storeManually(Request $request)
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
        ManagementAndStorageBilling::create([
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
        return response()->json(['status' => true, 'message' => 'ManagementAndStorageBilling created successfully'], 200);
    }
    
    public function indexSuperAdmin(Request $request)
    {
        try {
            // Get the authenticated user
            //$user_id = $request->user()->id;
            // Fetch billing related to the authenticated user
            $billingList = ManagementAndStorageBilling::get();
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
   
    public function show($billingId)
    {
        // Find the Project by ID
        $billing = ManagementAndStorageBilling::find($billingId);
        // Check if Project exists
        if (!$billing) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }
        // Return the Project data
        return response()->json(['status' => true, 'data' => $billing], 200);
    }
    public function edit(ManagementAndStorageBilling $billing)
    {
        //
    }
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
        $billing = ManagementAndStorageBilling::findOrFail($id);
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
