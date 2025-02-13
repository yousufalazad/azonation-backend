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
        // dd( $request-all()); exit;
        $validated = $request->validate([
            // Order fields
            'user_id' => 'required|integer',
            'user_name' => 'required|string|max:255',
            'order_code' => 'required|string|max:255',
            'total_amount' => 'required|numeric',
            'is_active' => 'required|boolean',

            // OrderItem fields
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
            DB::beginTransaction();

            // Create Order
            $order = Order::create([
                'user_id'         => $request->user_id ?? '', //$validated['user_id'],
                'billing_code'    => $request->billing_code ?? '',
                'order_code'      => $request->order_code ?? '',
                'order_date'      => $request->order_date ?? Carbon::now(),
                'user_name'       => $request->user_name ?? '', //$validated['user_name'],
                'sub_total'       => $request->sub_total ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'shipping_cost'   => $request->shipping_cost ?? 0,
                'total_tax'       => $request->total_tax ?? 0,
                'credit_applied'  => $request->credit_applied ?? '',
                'total_amount'    => $request->total_amount ?? '', //$validated['total_amount'],
                'discount_title'  => $request->discount_title ?? '',
                'tax_rate'        => $request->tax_rate ?? 0,
                'currency_code'   => $request->currency_code ?? '',
                'coupon_code'     => $request->coupon_code ?? '',
                'payment_method'  => $request->payment_method ?? '',
                'billing_address' => $request->billing_address ?? '',
                'user_country'    => $request->user_country ?? '',
                'user_region'     => $request->user_region ?? '',
                'is_active'       => $request->is_active ?? 1, //$validated['is_active'],
            ]);

            // Create Order Details
            OrderDetail::create([
                'order_id'              => $order->id,
                'shipping_address'      => $request->shipping_address ?? '',
                'shipping_status'       => $request->shipping_status ?? '',
                'shipping_method'       => $request->shipping_method ?? '',
                'shipping_note'         => $request->shipping_note ?? '',
                'customer_note'         => $request->customer_note ?? '',
                'admin_note'            => $request->admin_note ?? '',
                'tracking_number'       => $request->tracking_number ?? '',
                'delivery_date_expected' => $request->delivery_date_expected ?? now()->addDays(1),
                'delivery_date_actual'  => $request->delivery_date_actual ?? now()->addDays(1),
                'order_status'          => $request->order_status ?? '',
                'cancelled_at'          => $request->cancelled_at ?? null,
                'cancellation_reason'   => $request->cancellation_reason ?? '',
            ]);

            // Create Order Items
            foreach ($validated['order_items'] as $itemData) {
                $itemData['order_id'] = $order->id; // Associate order ID
                OrderItem::create($itemData);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Order created successfully.',
                'order'   => $order
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Error creating order.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        try {
            // Retrieve the Order with its associated OrderItems
            $order = Order::with(['orderDetail', 'orderItems'])->find($id);

            // $order = Order::with('orderItems')->findOrFail($id);

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
    

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            // Order fields
            'user_id' => 'required|integer',
            'user_name' => 'required|string|max:255',
            'order_code' => 'required|string|max:255',
            'total_amount' => 'required|numeric',
            'is_active' => 'required|boolean',

            // OrderItem fields
            'order_items' => 'required|array',
            'order_items.*.id' => 'nullable|integer',
            'order_items.*.product_id' => 'required|integer',
            'order_items.*.product_name' => 'required|string|max:255',
            'order_items.*.product_attributes' => 'nullable|string',
            'order_items.*.unit_price' => 'required|numeric',
            'order_items.*.quantity' => 'required|integer',
            'order_items.*.total_price' => 'required|numeric',
            'order_items.*.discount_amount' => 'nullable|numeric',
            'order_items.*.note' => 'nullable|string',
            'order_items.*.is_active' => 'nullable|boolean',

            // Order Details fields
            'shipping_address' => 'nullable|string',
            'shipping_status' => 'nullable|string',
            'shipping_method' => 'nullable|string',
            'shipping_note' => 'nullable|string',
            'customer_note' => 'nullable|string',
            'admin_note' => 'nullable|string',
            'tracking_number' => 'nullable|string',
            'delivery_date_expected' => 'nullable|date',
            'delivery_date_actual' => 'nullable|date',
            'order_status' => 'nullable|string',
            'cancelled_at' => 'nullable|date',
            'cancellation_reason' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Find the order
            $order = Order::findOrFail($id);

            // Update order
            $order->update([
                'user_id'         => $request->user_id,
                'billing_code'    => $request->billing_code ?? $order->billing_code,
                'order_code'      => $request->order_code,
                'order_date'      => $request->order_date ?? $order->order_date,
                'user_name'       => $request->user_name,
                'sub_total'       => $request->sub_total ?? $order->sub_total,
                'discount_amount' => $request->discount_amount ?? $order->discount_amount,
                'shipping_cost'   => $request->shipping_cost ?? $order->shipping_cost,
                'total_tax'       => $request->total_tax ?? $order->total_tax,
                'credit_applied'  => $request->credit_applied ?? $order->credit_applied,
                'total_amount'    => $request->total_amount,
                'discount_title'  => $request->discount_title ?? $order->discount_title,
                'tax_rate'        => $request->tax_rate ?? $order->tax_rate,
                'currency_code'   => $request->currency_code ?? $order->currency_code,
                'coupon_code'     => $request->coupon_code ?? $order->coupon_code,
                'payment_method'  => $request->payment_method ?? $order->payment_method,
                'billing_address' => $request->billing_address ?? $order->billing_address,
                'user_country'    => $request->user_country ?? $order->user_country,
                'user_region'     => $request->user_region ?? $order->user_region,
                'is_active'       => $request->is_active,
            ]);

            // Update or create OrderDetail
            if ($order->orderDetail) {
                $order->orderDetail->update([
                    'shipping_address' => $request->shipping_address ?? $order->orderDetail->shipping_address,
                    'shipping_status'  => $request->shipping_status ?? $order->orderDetail->shipping_status,
                    'shipping_method'  => $request->shipping_method ?? $order->orderDetail->shipping_method,
                    'shipping_note'    => $request->shipping_note ?? $order->orderDetail->shipping_note,
                    'customer_note'    => $request->customer_note ?? $order->orderDetail->customer_note,
                    'admin_note'       => $request->admin_note ?? $order->orderDetail->admin_note,
                    'tracking_number'  => $request->tracking_number ?? $order->orderDetail->tracking_number,
                    'delivery_date_expected' => $request->delivery_date_expected ?? $order->orderDetail->delivery_date_expected,
                    'delivery_date_actual' => $request->delivery_date_actual ?? $order->orderDetail->delivery_date_actual,
                    'order_status'     => $request->order_status ?? $order->orderDetail->order_status,
                    'cancelled_at'     => $request->cancelled_at ?? $order->orderDetail->cancelled_at,
                    'cancellation_reason' => $request->cancellation_reason ?? $order->orderDetail->cancellation_reason,
                ]);
            } else {
                OrderDetail::create([
                    'order_id'        => $order->id,
                    'shipping_address' => $request->shipping_address,
                    'shipping_status'  => $request->shipping_status,
                    'shipping_method'  => $request->shipping_method,
                    'shipping_note'    => $request->shipping_note,
                    'customer_note'    => $request->customer_note,
                    'admin_note'       => $request->admin_note,
                    'tracking_number'  => $request->tracking_number,
                    'delivery_date_expected' => $request->delivery_date_expected ?? now()->addDays(1),
                    'delivery_date_actual' => $request->delivery_date_actual ?? now()->addDays(1),
                    'order_status'     => $request->order_status,
                    'cancelled_at'     => $request->cancelled_at,
                    'cancellation_reason' => $request->cancellation_reason,
                ]);
            }

            // Update Order Items
            foreach ($validated['order_items'] as $itemData) {
                if (isset($itemData['id']) && $itemData['id']) {
                    // Update existing item
                    $orderItem = OrderItem::find($itemData['id']);
                    if ($orderItem) {
                        $orderItem->update([
                            'product_id' => $itemData['product_id'],
                            'product_name' => $itemData['product_name'],
                            'product_attributes' => $itemData['product_attributes'] ?? null,
                            'unit_price' => $itemData['unit_price'],
                            'quantity' => $itemData['quantity'],
                            'total_price' => $itemData['total_price'],
                            'discount_amount' => $itemData['discount_amount'] ?? 0,
                            'note' => $itemData['note'] ?? null,
                            'is_active' => $itemData['is_active'] ?? 1,
                        ]);
                    }
                } else {
                    // Create new item
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $itemData['product_id'],
                        'product_name' => $itemData['product_name'],
                        'product_attributes' => $itemData['product_attributes'] ?? null,
                        'unit_price' => $itemData['unit_price'],
                        'quantity' => $itemData['quantity'],
                        'total_price' => $itemData['total_price'],
                        'discount_amount' => $itemData['discount_amount'] ?? 0,
                        'note' => $itemData['note'] ?? null,
                        'is_active' => $itemData['is_active'] ?? 1,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Order updated successfully.',
                'order'   => $order
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order update failed: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Error updating order.',
                'error'   => $e->getMessage()
            ], 500);
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
