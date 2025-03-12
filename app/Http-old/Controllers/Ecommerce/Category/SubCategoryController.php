<?php
namespace App\Http\Controllers\Ecommerce\Category;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Storage;

class SubCategoryController extends Controller
{
    public function index()
    {
        try {
            $subCategories = SubCategory::all();
            return response()->json(['status' => true, 'data' => $subCategories], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error fetching sub categories.'], 500);
        }
    }
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
        ]);
        try {
            $subCategory = new SubCategory($validated);
            $subCategory->save();
            return response()->json(['status' => true, 'message' => 'Sub Category created successfully.', 'data' => $subCategory], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error creating sub category.'], 500);
        }
    }
    public function show($id)
    {
        try {
            $subCategory = SubCategory::findOrFail($id);
            return response()->json(['status' => true, 'data' => $subCategory], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Sub Category not found.'], 404);
        }
    }
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
        ]);
        try {
            $subCategory = SubCategory::findOrFail($id);
            $subCategory->fill($validated);
            $subCategory->save();
            return response()->json(['status' => true, 'message' => 'Sub Category updated successfully.', 'data' => $subCategory], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error updating sub category.'], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $subCategory = SubCategory::findOrFail($id);
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
