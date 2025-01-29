<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Storage;

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

    public function managementAndStorageBillingOrder()
    {

        

        // Get all users with active storage subscriptions
        // $users = User::with('storageSubscription')->whereHas('storageSubscription', function ($query) {
        //     $query->where('is_active', true);
        // })->get();

        // Process billing for each user
        // foreach ($users as $user) {
        //     $billingData = $this->getUserStorageDailyPriceRate($user->id);
        //     $this->storeBillingRecord($billingData);
        // }

        // return response()->json(['status' => true, 'message' => 'Billing records generated successfully.'], 200);
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
