<?php
namespace App\Http\Controllers\Ecommerce\Category;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = Category::all();
            return response()->json(['status' => true, 'data' => $categories], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error fetching categories.'], 500);
        }
    }
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
        ]);
        try {
            $category = new Category($validated);
            $category->save();
            return response()->json(['status' => true, 'message' => 'Category created successfully.', 'data' => $category], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error creating category.'], 500);
        }
    }
    public function show($id)
    {
        try {
            $category = Category::findOrFail($id);
            return response()->json(['status' => true, 'data' => $category], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Category not found.'], 404);
        }
    }
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
        ]);
        try {
            $category = Category::findOrFail($id);
            $category->fill($validated);
            $category->save();
            return response()->json(['status' => true, 'message' => 'Category updated successfully.', 'data' => $category], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error updating category.'], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);
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
