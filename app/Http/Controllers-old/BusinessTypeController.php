<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BusinessType;
use Illuminate\Support\Facades\Storage;

class BusinessTypeController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        try {
            $businessTypes = BusinessType::all();
            return response()->json(['status' => true, 'data' => $businessTypes], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error fetching Business Types.'], 500);
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
            'slug' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'required|boolean',
            // 'business_type_image_path' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            $businessType = new BusinessType($validated);

            // Handle image upload
            // if ($request->hasFile('business_type_image_path')) {
            //     $file = $request->file('business_type_image_path');
            //     $path = $file->store('categories', 'public');
            //     $businessType->business_type_image_path = $path;
            // }

            $businessType->save();
            return response()->json(['status' => true, 'message' => 'Business Type created successfully.', 'data' => $businessType], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error creating Business Type.'], 500);
        }
    }

    /**
     * Display the specified category.
     */
    public function show($id)
    {
        try {
            $businessType = BusinessType::findOrFail($id);
            return response()->json(['status' => true, 'data' => $businessType], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Business Type not found.'], 404);
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
            'slug' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'required|boolean',
            // 'business_type_image_path' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            $businessType = BusinessType::findOrFail($id);

            $businessType->fill($validated);

            // Handle image upload
            // if ($request->hasFile('business_type_image_path')) {
            //     // Delete old image if exists
            //     if ($businessType->business_type_image_path) {
            //         Storage::disk('public')->delete($businessType->business_type_image_path);
            //     }

            //     $file = $request->file('business_type_image_path');
            //     $path = $file->store('categories', 'public');
            //     $businessType->business_type_image_path = $path;
            // }

            $businessType->save();
            return response()->json(['status' => true, 'message' => 'Business Type updated successfully.', 'data' => $businessType], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error updating Business Type.'], 500);
        }
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id)
    {
        try {
            $businessType = BusinessType::findOrFail($id);

            // Delete image if exists
            if ($businessType->business_type_image_path) {
                Storage::disk('public')->delete($businessType->business_type_image_path);
            }

            $businessType->delete();
            return response()->json(['status' => true, 'message' => 'Business Type deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error deleting Business Type.'], 500);
        }
    }
}
