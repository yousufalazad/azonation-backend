<?php

namespace App\Http\Controllers;

use App\Models\ManagementAndStorageBilling;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index()
    {
        try {
            $orders = Order::all();
            return response()->json(['status' => true, 'data' => $orders], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error fetching products.'], 500);
        }
    }


    public function generateOrdersFromBillings()
    {
        try {
            DB::transaction(function () {

                // Fetch all ManagementAndStorageBilling 
                $billings = ManagementAndStorageBilling::where('bill_status', 'issued')->get();

                foreach ($billings as $billing) {
                    $user = $billing->user_id;
                    if (!$user) {
                        continue; // Skip if user is not found
                    }

                    // Create an order
                    $order = Order::create([
                        'user_id'        => $billing->user_id,
                        'billing_code'   => $billing->billing_code,
                        'order_date'     => Carbon::now(),
                        'user_name'      => $billing->user_name,
                        'sub_total'      => $billing->total_management_bill_amount + $billing->total_storage_bill_amount,
                        'discount_amount' => 0, // Default to no discount
                        'shipping_cost'  => 0, // No shipping cost for service-based billing
                        'total_tax'      => 0, // No tax applied initially
                        'credit_applied' => 0, // No credit applied initially
                        'total_amount'   => $billing->total_management_bill_amount + $billing->total_storage_bill_amount,
                        'discount_title' => null,
                        'tax_rate'       => null,
                        'currency_code'  => $billing->currency_code,
                        'coupon_code'    => null,
                        'payment_method' => 'Bank Transfer',
                        'billing_address' => 'User registered billing address',
                        'user_country'   => $user->country ?? 'Unknown',
                        'user_region'    => $user->region ?? 'Unknown',
                        'is_active'      => true
                    ]);

                    if (!$order) {
                        Log::error('Order creation failed for user: ' . $billing->user_id);
                        continue;
                    }

                    // Retrieve related products for the billing
                    $managementPortalSubscriptionProduct = Product::where('id', 1)->first();
                    if ($managementPortalSubscriptionProduct && $managementPortalSubscriptionProduct->id == 1) {
                        OrderItem::create([
                            'order_id'          => $order->id,
                            'product_id'        => $managementPortalSubscriptionProduct->id,
                            'product_name'      => $managementPortalSubscriptionProduct->name,
                            'product_attributes' => null,
                            'unit_price'        => $billing->total_management_bill_amount,
                            'quantity'          => 1,
                            'total_price'       => $billing->total_management_bill_amount * 1,
                            'discount_amount'   => 0,
                            'note'              => 'Auto generated from management billing data for this order',
                            'is_active'         => true
                        ]);
                    } else {
                        return response()->json(['status' => false, 'message' => 'Product not found.'], 404);
                    }

                    // Retrieve related products for the storage
                    $storageSubscriptionProduct = Product::where('id', 3)->first();
                    if ($storageSubscriptionProduct && $storageSubscriptionProduct->id == 3) {
                        OrderItem::create([
                            'order_id'          => $order->id,
                            'product_id'        => $storageSubscriptionProduct->id,
                            'product_name'      => $storageSubscriptionProduct->name,
                            'product_attributes' => null,
                            'unit_price'        => $billing->total_storage_bill_amount,
                            'quantity'          => 1,
                            'total_price'       => $billing->total_storage_bill_amount * 1,
                            'discount_amount'   => 0,
                            'note'              => 'Auto generated from management billing data for this order',
                            'is_active'         => true
                        ]);
                    } else {
                        return response()->json(['status' => false, 'message' => 'Product not found.'], 404);
                    }

                    // Create order details
                    $orderDetail = OrderDetail::create([
                        'order_id'          => $order->id,
                        'shipping_address'  => 'Not applicable',
                        'shipping_status'   => 'pending',
                        'shipping_method'   => 'Standard Shipping',
                        'shipping_note'     => 'Auto-generated order from management billing data for this order',
                        'customer_note'     => null,
                        'admin_note'        => 'Admin generated',
                        'tracking_number'   => null,
                        'delivery_date_expected' => now()->addDays(1),
                        'delivery_date_actual'   => null,
                        'order_status'      => 'pending',
                        'cancelled_at'      => null,
                        'cancellation_reason' => null
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
        }
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            // Order fields
            'user_id' => 'required|integer',
            'user_name' => 'required|string|max:255',
            'order_number' => 'required|string|max:255',
            'total_amount' => 'required|numeric',
            'discount_amount' => 'nullable|numeric',
            'shipping_cost' => 'nullable|numeric',
            'total_tax' => 'nullable|numeric',
            'currency' => 'nullable|string|max:10',
            'shipping_status' => 'nullable|string|max:255',
            'shipping_address' => 'required|string',
            'billing_address' => 'required|string',
            'coupon_code' => 'nullable|string|max:255',
            'shipping_method' => 'nullable|string|max:255',
            'shipping_note' => 'nullable|string',
            'customer_note' => 'nullable|string',
            'admin_note' => 'nullable|string',
            'tracking_number' => 'nullable|string|max:255',
            'order_date' => 'required|date',
            'delivery_date_expected' => 'nullable|date',
            'delivery_date_actual' => 'nullable|date',
            'status' => 'required|string|max:50',
            'cancelled_at' => 'nullable|date',
            'is_active' => 'required|boolean',

            //OrderItem fields
            'order_items' => 'required|array',
            'order_items.*.product_id' => 'required|integer',
            'order_items.*.product_name' => 'required|string|max:255',
            'order_items.*.product_attributes' => 'nullable|string',
            'order_items.*.unit_price' => 'required|numeric',
            'order_items.*.quantity' => 'required|integer',
            'order_items.*.total_price' => 'required|numeric',
            'order_items.*.discount_amount' => 'nullable|numeric',
            'order_items.*.note' => 'nullable|string',
            'order_items.*.is_active' => 'nullable|boolean',
        ]);

        try {
            // Create a new Order instance
            $orderData = $validated;
            unset($orderData['order_items']); // Remove order items for main Order model
            $order = Order::create($orderData);

            // Process and save Order Items
            foreach ($validated['order_items'] as $itemData) {
                $itemData['order_id'] = $order->id; // Associate the order ID
                OrderItem::create($itemData);
            }

            return response()->json(['status' => true, 'message' => 'Order created successfully.', 'data' => $order], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error creating order.', 'error' => $e->getMessage()], 500);
        }
    }


    /**
     * Display the specified product.
     */
    public function show($id)
    {
        try {
            // Retrieve the Order with its associated OrderItems
            $order = Order::with('orderItems')->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Order details retrieved successfully.',
                'data' => $order,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving order details.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            // Order fields
            'user_id' => 'required|integer',
            'user_name' => 'required|string|max:255',
            'order_number' => 'required|string|max:255',
            'total_amount' => 'required|numeric',
            'discount_amount' => 'nullable|numeric',
            'shipping_cost' => 'nullable|numeric',
            'total_tax' => 'nullable|numeric',
            'currency' => 'nullable|string|max:10',
            'shipping_status' => 'nullable|string|max:255',
            'shipping_address' => 'required|string',
            'billing_address' => 'required|string',
            'coupon_code' => 'nullable|string|max:255',
            'shipping_method' => 'nullable|string|max:255',
            'shipping_note' => 'nullable|string',
            'customer_note' => 'nullable|string',
            'admin_note' => 'nullable|string',
            'tracking_number' => 'nullable|string|max:255',
            'order_date' => 'required|date',
            'delivery_date_expected' => 'nullable|date',
            'delivery_date_actual' => 'nullable|date',
            'status' => 'required|string|max:50',
            'cancelled_at' => 'nullable|date',
            'is_active' => 'required|boolean',

            // OrderItem fields
            'order_items' => 'required|array',
            'order_items.*.id' => 'nullable|integer', // For existing items
            'order_items.*.product_id' => 'required|integer',
            'order_items.*.product_name' => 'nullable|string|max:255',
            'order_items.*.product_attributes' => 'nullable|string',
            'order_items.*.unit_price' => 'required|numeric',
            'order_items.*.quantity' => 'required|integer',
            'order_items.*.total_price' => 'required|numeric',
            'order_items.*.discount_amount' => 'nullable|numeric',
            'order_items.*.note' => 'nullable|string',
            'order_items.*.is_active' => 'nullable|boolean',
        ]);

        try {
            // Find the Order
            $order = Order::findOrFail($id);

            // Update Order fields
            $orderData = $validated;
            unset($orderData['order_items']); // Remove order items for main Order model
            $order->update($orderData);

            // Process Order Items
            $existingItems = $order->orderItems()->pluck('id')->toArray();
            $submittedItems = collect($validated['order_items']);

            // // Update or create submitted items
            foreach ($submittedItems as $itemData) {
                if (isset($itemData['id']) && in_array($itemData['id'], $existingItems)) {
                    // Update existing item
                    $orderItem = OrderItem::find($itemData['id']);
                    $orderItem->update($itemData);
                } else {
                    // Create new item
                    $itemData['order_id'] = $order->id;
                    OrderItem::create($itemData);
                }
            }

            // Delete removed items
            $submittedItemIds = $submittedItems->pluck('id')->filter()->toArray();
            $itemsToDelete = array_diff($existingItems, $submittedItemIds);
            OrderItem::destroy($itemsToDelete);

            return response()->json(['status' => true, 'message' => 'Order updated successfully.', 'data' => $order], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error updating order.', 'error' => $e->getMessage()], 500);
        }
    }



    /**
     * Remove the specified product from storage.
     */
    public function destroy($id)
    {
        try {
            // Find the Order
            $order = Order::findOrFail($id);

            // Delete all associated OrderItems
            $order->orderItems()->delete();

            // Delete the Order itself
            $order->delete();

            return response()->json(['status' => true, 'message' => 'Order and associated items deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error deleting order.', 'error' => $e->getMessage()], 500);
        }
    }
}
