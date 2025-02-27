<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        try {
            $categories = Category::all();
            return response()->json(['status' => true, 'data' => $categories], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error fetching categories.'], 500);
        }
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'business_type_id' => 'required|integer',
            'slug' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'required|boolean',
            // 'category_image_path' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            $category = new Category($validated);

            // Handle image upload
            // if ($request->hasFile('category_image_path')) {
            //     $file = $request->file('category_image_path');
            //     $path = $file->store('categories', 'public');
            //     $category->category_image_path = $path;
            // }

            $category->save();
            return response()->json(['status' => true, 'message' => 'Category created successfully.', 'data' => $category], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error creating category.'], 500);
        }
    }

    /**
     * Display the specified category.
     */
    public function show($id)
    {
        try {
            $category = Category::findOrFail($id);
            return response()->json(['status' => true, 'data' => $category], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Category not found.'], 404);
        }
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'business_type_id' => 'required|integer',
            'slug' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'required|boolean',
            // 'category_image_path' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            $category = Category::findOrFail($id);

            $category->fill($validated);

            // Handle image upload
            // if ($request->hasFile('category_image_path')) {
            //     // Delete old image if exists
            //     if ($category->category_image_path) {
            //         Storage::disk('public')->delete($category->category_image_path);
            //     }

            //     $file = $request->file('category_image_path');
            //     $path = $file->store('categories', 'public');
            //     $category->category_image_path = $path;
            // }

            $category->save();
            return response()->json(['status' => true, 'message' => 'Category updated successfully.', 'data' => $category], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error updating category.'], 500);
        }
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);

            // Delete image if exists
            if ($category->category_image_path) {
                Storage::disk('public')->delete($category->category_image_path);
            }

            $category->delete();
            return response()->json(['status' => true, 'message' => 'Category deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error deleting category.'], 500);
        }
    }
}
