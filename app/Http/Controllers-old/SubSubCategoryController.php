<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubSubCategory;
use Illuminate\Support\Facades\Storage;

class SubSubCategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        try {
            $subSubCategories = SubSubCategory::all();
            return response()->json(['status' => true, 'data' => $subSubCategories], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error fetching sub sub categories.'], 500);
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
            'sub_category_id' => 'required|integer',
            'slug' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'required|boolean',
            // 'sub_sub_category_image_path' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            $subSubCategory = new SubSubCategory($validated);

            // Handle image upload
            // if ($request->hasFile('sub_sub_category_image_path')) {
            //     $file = $request->file('sub_sub_category_image_path');
            //     $path = $file->store('categories', 'public');
            //     $subSubCategory->sub_sub_category_image_path = $path;
            // }

            $subSubCategory->save();
            return response()->json(['status' => true, 'message' => 'Sub Sub Category created successfully.', 'data' => $subSubCategory], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error creating sub sub category.'], 500);
        }
    }

    /**
     * Display the specified category.
     */
    public function show($id)
    {
        try {
            $subSubCategory = SubSubCategory::findOrFail($id);
            return response()->json(['status' => true, 'data' => $subSubCategory], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Sub Sub Category not found.'], 404);
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
            'sub_category_id' => 'required|integer',
            'slug' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'required|boolean',
            // 'sub_sub_category_image_path' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            $subSubCategory = SubSubCategory::findOrFail($id);

            $subSubCategory->fill($validated);

            // Handle image upload
            // if ($request->hasFile('sub_sub_category_image_path')) {
            //     // Delete old image if exists
            //     if ($subSubCategory->sub_sub_category_image_path) {
            //         Storage::disk('public')->delete($subSubCategory->sub_sub_category_image_path);
            //     }

            //     $file = $request->file('sub_sub_category_image_path');
            //     $path = $file->store('categories', 'public');
            //     $subSubCategory->sub_sub_category_image_path = $path;
            // }

            $subSubCategory->save();
            return response()->json(['status' => true, 'message' => 'Sub Sub Category updated successfully.', 'data' => $subSubCategory], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error updating sub sub category.'], 500);
        }
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id)
    {
        try {
            $subSubCategory = SubSubCategory::findOrFail($id);

            // Delete image if exists
            if ($subSubCategory->sub_sub_category_image_path) {
                Storage::disk('public')->delete($subSubCategory->sub_sub_category_image_path);
            }

            $subSubCategory->delete();
            return response()->json(['status' => true, 'message' => 'Sub Sub Category deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error deleting sub sub category.'], 500);
        }
    }
}