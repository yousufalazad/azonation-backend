<?php
namespace App\Http\Controllers\Ecommerce\Category;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\SubSubCategory;
use Illuminate\Support\Facades\Storage;

class SubSubCategoryController extends Controller
{
    public function index()
    {
        try {
            $subSubCategories = SubSubCategory::all();
            return response()->json(['status' => true, 'data' => $subSubCategories], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error fetching sub sub categories.'], 500);
        }
    }
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
        ]);
        try {
            $subSubCategory = new SubSubCategory($validated);
            $subSubCategory->save();
            return response()->json(['status' => true, 'message' => 'Sub Sub Category created successfully.', 'data' => $subSubCategory], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error creating sub sub category.'], 500);
        }
    }
    public function show($id)
    {
        try {
            $subSubCategory = SubSubCategory::findOrFail($id);
            return response()->json(['status' => true, 'data' => $subSubCategory], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Sub Sub Category not found.'], 404);
        }
    }
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
        ]);
        try {
            $subSubCategory = SubSubCategory::findOrFail($id);
            $subSubCategory->fill($validated);
            $subSubCategory->save();
            return response()->json(['status' => true, 'message' => 'Sub Sub Category updated successfully.', 'data' => $subSubCategory], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error updating sub sub category.'], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $subSubCategory = SubSubCategory::findOrFail($id);
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
