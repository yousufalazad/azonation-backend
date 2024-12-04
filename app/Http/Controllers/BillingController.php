<?php

namespace App\Http\Controllers;
use App\Models\Billing;
use App\Models\ActiveMemberCount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BillingController extends Controller
{
    public function memberQuantity($userId)
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        $activeMemberCounts = ActiveMemberCount::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        return response()->json([
            'status' => true,
            'total_active_member' => $activeMemberCounts->sum('active_member'),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'service_month' => $startDate->format('F'), // Service month name
            'billing_month' => $startDate->addMonth()->format('F'), // Billing month
        ]);
    }

    public function regionalPrice($userId)
    {
        $regionalPrice = User::query()
            ->where('users.id', $userId)
            ->leftJoin('user_countries', 'users.id', '=', 'user_countries.user_id')
            ->leftJoin('country_regions', 'user_countries.country_id', '=', 'country_regions.country_id')
            ->leftJoin('subscriptions', 'users.id', '=', 'subscriptions.user_id')
            ->leftJoin('regional_pricings', function ($join) {
                $join->on('regional_pricings.region_id', '=', 'country_regions.region_id')
                    ->on('regional_pricings.package_id', '=', 'subscriptions.package_id');
            })
            ->select('regional_pricings.price as regional_price_rate')
            ->first();

        $price = $regionalPrice ? $regionalPrice->regional_price_rate : 0;

        return response()->json([
            'status' => true,
            'regional_price_rate' => $price
        ]);
    }


    public function billCalculation()
    {
        try {
            // $users = User::where('type', 'organisation')->get();
            $users = User::get();

            $billingData = $users->map(function ($user) {
                $userId = $user->id;

                $activeMemberResponse = $this->memberQuantity($userId);
                $totalActiveMemberData = $activeMemberResponse->getData(true);

                $priceRateResponse = $this->regionalPrice($userId);
                $priceRate = $priceRateResponse->getData(true);

                $billAmount = $totalActiveMemberData['total_active_member'] * $priceRate['regional_price_rate'];

                return [
                    'user_id' => $userId,
                    'user_name' => $user->name,
                    'description' => 'Azonation subscription fee',
                    'billing_address' => $user->name, // Assuming billing address field
                    'item_name' => 'Azonation subscription fee',
                    'period_start' => $totalActiveMemberData['start_date'],
                    'period_end' => $totalActiveMemberData['end_date'],
                    'service_month' => $totalActiveMemberData['service_month'],
                    'billing_month' => $totalActiveMemberData['billing_month'],
                    'total_active_member' => $totalActiveMemberData['total_active_member'],
                    'total_billable_active_member' => $totalActiveMemberData['total_active_member'],
                    'price_rate' => $priceRate['regional_price_rate'],
                    'bill_amount' => $billAmount,
                    'status' => 'issued',
                    'admin_notes' => 'non-refundable',
                    'is_active' => 1,
                ];
            });

            return response()->json([
                'status' => true,
                'data' => $billingData,
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
        try {
            $billingResponse = $this->billCalculation();
            $billingData = $billingResponse->getData(true); // Converts JSON response to an associative array
            //$billingData = $billingResponse;   //Cannot use object of type Illuminate\Http\JsonResponse as array

            foreach ($billingData['data'] as $bill) {
                Billing::create([
                    'user_id' => $bill['user_id'],
                    'user_name' => 'Azon',
                    'description' => 'Azonation subscription fee, depends on package and total member',
                    'billing_address' => 'Azon',
                    'item_name' => 'Azonation subscription fee',
                    'period_start' => $bill['period_start'],
                    'period_end' => $bill['period_end'],
                    'service_month' => $bill['service_month'],
                    'billing_month' => $bill['billing_month'],
                    'total_active_member' => $bill['total_active_member'],
                    'total_billable_active_member' => $bill['total_active_member'],
                    'price_rate' => $bill['price_rate'],
                    'bill_amount' => $bill['bill_amount'],
                    'status' => 'issued',
                    'admin_notes' => 'non-refundable',
                    'is_active' => 1,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error generating billing: ' . $e->getMessage());
        }
    }

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

    public function indexSuperAdmin(Request $request)
    {
        try {
            // Get the authenticated user
            //$user_id = $request->user()->id;

            // Fetch billing related to the authenticated user
            $billingList = Billing::get();

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

    public function create()
    {
        //
    }


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


    public function edit(Billing $billing)
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
