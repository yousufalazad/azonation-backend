<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Storage;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        try {
            $subCategories = SubCategory::all();
            return response()->json(['status' => true, 'data' => $subCategories], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error fetching sub categories.'], 500);
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
            'category_id' => 'required|integer',
            'slug' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'required|boolean',
            // 'sub_category_image_path' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            $subCategory = new SubCategory($validated);

            // Handle image upload
            // if ($request->hasFile('sub_category_image_path')) {
            //     $file = $request->file('sub_category_image_path');
            //     $path = $file->store('categories', 'public');
            //     $subCategory->sub_category_image_path = $path;
            // }

            $subCategory->save();
            return response()->json(['status' => true, 'message' => 'Sub Category created successfully.', 'data' => $subCategory], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error creating sub category.'], 500);
        }
    }

    /**
     * Display the specified category.
     */
    public function show($id)
    {
        try {
            $subCategory = SubCategory::findOrFail($id);
            return response()->json(['status' => true, 'data' => $subCategory], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Sub Category not found.'], 404);
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
            'category_id' => 'required|integer',
            'slug' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'required|boolean',
            // 'sub_category_image_path' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            $subCategory = SubCategory::findOrFail($id);

            $subCategory->fill($validated);

            // Handle image upload
            // if ($request->hasFile('sub_category_image_path')) {
            //     // Delete old image if exists
            //     if ($subCategory->sub_category_image_path) {
            //         Storage::disk('public')->delete($subCategory->sub_category_image_path);
            //     }

            //     $file = $request->file('sub_category_image_path');
            //     $path = $file->store('categories', 'public');
            //     $subCategory->sub_category_image_path = $path;
            // }

            $subCategory->save();
            return response()->json(['status' => true, 'message' => 'Sub Category updated successfully.', 'data' => $subCategory], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error updating sub category.'], 500);
        }
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id)
    {
        try {
            $subCategory = SubCategory::findOrFail($id);

            // Delete image if exists
            if ($subCategory->sub_category_image_path) {
                Storage::disk('public')->delete($subCategory->sub_category_image_path);
            }

            $subCategory->delete();
            return response()->json(['status' => true, 'message' => 'Sub Category deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error deleting sub category.'], 500);
        }
    }
}