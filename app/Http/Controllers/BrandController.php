<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $brands = Brand::all();
            return response()->json(['status' => true, 'data' => $brands], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error fetching brands.'], 500);
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
            'is_active' => 'required|boolean',
            // 'logo_path' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            $brand = new Brand($validated);

            // Handle image upload
            // if ($request->hasFile('logo_path')) {
            //     $file = $request->file('logo_path');
            //     $path = $file->store('categories', 'public');
            //     $brand->logo_path = $path;
            // }

            $brand->save();
            return response()->json(['status' => true, 'message' => 'Brand created successfully.', 'data' => $brand], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error creating brand.'], 500);
        }
    }

    /**
     * Display the specified category.
     */
    public function show($id)
    {
        try {
            $brand = Brand::findOrFail($id);
            return response()->json(['status' => true, 'data' => $brand], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Brand not found.'], 404);
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
            'is_active' => 'required|boolean',
            // 'logo_path' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            $brand = Brand::findOrFail($id);

            $brand->fill($validated);

            // Handle image upload
            // if ($request->hasFile('logo_path')) {
            //     // Delete old image if exists
            //     if ($brand->logo_path) {
            //         Storage::disk('public')->delete($brand->logo_path);
            //     }

            //     $file = $request->file('logo_path');
            //     $path = $file->store('categories', 'public');
            //     $brand->logo_path = $path;
            // }

            $brand->save();
            return response()->json(['status' => true, 'message' => 'Brand updated successfully.', 'data' => $brand], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error updating brand.'], 500);
        }
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id)
    {
        try {
            $brand = Brand::findOrFail($id);

            // Delete image if exists
            if ($brand->logo_path) {
                Storage::disk('public')->delete($brand->logo_path);
            }

            $brand->delete();
            return response()->json(['status' => true, 'message' => 'Brand deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error deleting brand.'], 500);
        }
    }
}
