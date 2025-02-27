<?php
namespace App\Http\Controllers\Ecommerce\Product;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::all();
            return response()->json(['status' => true, 'data' => $products], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error fetching products.'], 500);
        }
    }
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
            'attributes' => 'nullable',
            'weight' => 'nullable|numeric',
            'dimensions' => 'nullable|string',
            'additional_information' => 'nullable|string',
            'is_in_stock' => 'nullable|boolean',
            'sold_quantity' => 'nullable|integer',
            'additional_shipping_info' => 'nullable|string',
            'shipping_rules' => 'nullable',
            'tags' => 'nullable|string',
            'warranty_period' => 'nullable',
            'is_active' => 'required|boolean',
        ]);
        try {
            $product = new Product($validated);
            if ($request->has('attributes')) {
                $product->attributes = json_encode($request->attributes);
            }
            if ($request->has('shipping_rules')) {
                $product->shipping_rules = json_encode($request->shipping_rules);
            }
            $product->save();
            return response()->json(['status' => true, 'message' => 'Product created successfully.', 'data' => $product], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error creating product.'], 500);
        }
    }
    public function show($id)
    {
        try {
            $product = Product::findOrFail($id);
            return response()->json(['status' => true, 'data' => $product], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Product not found.'], 404);
        }
    }
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
            'attributes' => 'nullable',
            'weight' => 'nullable|numeric',
            'dimensions' => 'nullable|string',
            'additional_information' => 'nullable|string',
            'is_in_stock' => 'nullable|boolean',
            'sold_quantity' => 'nullable|integer',
            'additional_shipping_info' => 'nullable|string',
            'shipping_rules' => 'nullable',
            'tags' => 'nullable|string',
            'warranty_period' => 'nullable',
            'is_active' => 'required|boolean',
        ]);
        try {
            $product = Product::findOrFail($id);
            $product->fill($validated);
            if ($request->has('attributes')) {
                $product->attributes = json_encode($request->attributes);
            }
            if ($request->has('shipping_rules')) {
                $product->shipping_rules = json_encode($request->shipping_rules);
            }
            $product->save();
            return response()->json(['status' => true, 'message' => 'Product updated successfully.', 'data' => $product], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error updating product.'], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
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
