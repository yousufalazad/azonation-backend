<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index()
    {
        try {
            $products = Product::all();
            return response()->json(['status' => true, 'data' => $products], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error fetching products.'], 500);
        }
    }

    /**
     * Store a newly created product in storage.
     */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'business_type_id' => 'required|integer',
            'category_id' => 'required|integer',
            'sub_category_id' => 'nullable|integer',
            'sub_sub_category_id' => 'nullable|integer',
            'brand_id' => 'nullable|integer',
            'sku' => 'required|string|max:255',
            'slug' => 'nullable|string',
            'short_description' => 'nullable|string',
            'invoice_description' => 'nullable|string',
            'base_price' => 'nullable|numeric',
            'on_sale' => 'nullable|boolean',
            'discount_percentage' => 'nullable|numeric',
            'sale_price' => 'nullable|numeric',
            'sale_start_date' => 'nullable|date',
            'sale_end_date' => 'nullable|date',
            'is_downloadable' => 'nullable|boolean',
            'download_link' => 'nullable|string|max:255',
            'is_gift_card' => 'nullable|boolean',
            'is_refundable' => 'nullable|boolean',
            'is_customizable' => 'nullable|boolean',
            'is_backorderable' => 'nullable|boolean',
            'is_sold_individually' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'stock_quantity' => 'nullable|integer',
            'is_featured' => 'nullable|boolean',
            'is_new' => 'nullable|boolean',
            // 'feature_image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            // 'attributes' => 'nullable|array',
            'attributes' => 'nullable',
            'weight' => 'nullable|numeric',
            'dimensions' => 'nullable|string',
            'additional_information' => 'nullable|string',
            'is_in_stock' => 'nullable|boolean',
            'sold_quantity' => 'nullable|integer',
            'additional_shipping_info' => 'nullable|string',
            // 'shipping_rules' => 'nullable|array',
            'shipping_rules' => 'nullable',
            'tags' => 'nullable|string',
            'warranty_period' => 'nullable',
            'is_active' => 'required|boolean',
        ]);

        try {
            // Create a new Product instance
            $product = new Product($validated);

            // // Handle feature image upload
            // if ($request->hasFile('feature_image')) {
            //     $file = $request->file('feature_image');
            //     $path = $file->store('products', 'public');
            //     $product->feature_image = $path;
            // }

            // Save attributes as JSON if provided
            if ($request->has('attributes')) {
                $product->attributes = json_encode($request->attributes);
            }

            // Handle shipping rules if provided
            if ($request->has('shipping_rules')) {
                $product->shipping_rules = json_encode($request->shipping_rules);
            }

            $product->save();

            return response()->json(['status' => true, 'message' => 'Product created successfully.', 'data' => $product], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error creating product.'], 500);
        }
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        try {
            $product = Product::findOrFail($id);
            return response()->json(['status' => true, 'data' => $product], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Product not found.'], 404);
        }
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'business_type_id' => 'required|integer',
            'category_id' => 'required|integer',
            'sub_category_id' => 'nullable|integer',
            'sub_sub_category_id' => 'nullable|integer',
            'brand_id' => 'nullable|integer',
            'sku' => 'required|string|max:255',
            'slug' => 'nullable|string',
            'short_description' => 'nullable|string',
            'invoice_description' => 'nullable|string',
            'base_price' => 'nullable|numeric',
            'on_sale' => 'nullable|boolean',
            'discount_percentage' => 'nullable|numeric',
            'sale_price' => 'nullable|numeric',
            'sale_start_date' => 'nullable|date',
            'sale_end_date' => 'nullable|date',
            'is_downloadable' => 'nullable|boolean',
            'download_link' => 'nullable|string|max:255',
            'is_gift_card' => 'nullable|boolean',
            'is_refundable' => 'nullable|boolean',
            'is_customizable' => 'nullable|boolean',
            'is_backorderable' => 'nullable|boolean',
            'is_sold_individually' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'stock_quantity' => 'nullable|integer',
            'is_featured' => 'nullable|boolean',
            'is_new' => 'nullable|boolean',
            // 'feature_image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            // 'attributes' => 'nullable|array',
            'attributes' => 'nullable',
            'weight' => 'nullable|numeric',
            'dimensions' => 'nullable|string',
            'additional_information' => 'nullable|string',
            'is_in_stock' => 'nullable|boolean',
            'sold_quantity' => 'nullable|integer',
            'additional_shipping_info' => 'nullable|string',
            // 'shipping_rules' => 'nullable|array',
            'shipping_rules' => 'nullable',
            'tags' => 'nullable|string',
            'warranty_period' => 'nullable',
            'is_active' => 'required|boolean',
        ]);

        try {
            // Find the product by ID
            $product = Product::findOrFail($id);

            // Update attributes
            $product->fill($validated);

            // Handle attributes field if provided
            if ($request->has('attributes')) {
                $product->attributes = json_encode($request->attributes);
            }

            // Handle shipping rules if provided
            if ($request->has('shipping_rules')) {
                $product->shipping_rules = json_encode($request->shipping_rules);
            }

            $product->save();

            return response()->json(['status' => true, 'message' => 'Product updated successfully.', 'data' => $product], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error updating product.'], 500);
        }
    }


    /**
     * Remove the specified product from storage.
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);

            // Delete image if exists
            if ($product->product_image_path) {
                Storage::disk('public')->delete($product->product_image_path);
            }

            $product->delete();
            return response()->json(['status' => true, 'message' => 'Product deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error deleting product.'], 500);
        }
    }
}
