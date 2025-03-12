<?php
namespace App\Http\Controllers\Ecommerce;
use App\Http\Controllers\Controller;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    public function index()
    {
        try {
            $brands = Brand::all();
            return response()->json(['status' => true, 'data' => $brands], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error fetching brands.'], 500);
        }
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);
        try {
            $brand = new Brand($validated);
            $brand->save();
            return response()->json(['status' => true, 'message' => 'Brand created successfully.', 'data' => $brand], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error creating brand.'], 500);
        }
    }
    public function show($id)
    {
        try {
            $brand = Brand::findOrFail($id);
            return response()->json(['status' => true, 'data' => $brand], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Brand not found.'], 404);
        }
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);
        try {
            $brand = Brand::findOrFail($id);
            $brand->fill($validated);
            $brand->save();
            return response()->json(['status' => true, 'message' => 'Brand updated successfully.', 'data' => $brand], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error updating brand.'], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $brand = Brand::findOrFail($id);
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
