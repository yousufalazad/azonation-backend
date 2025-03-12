<?php
namespace App\Http\Controllers\Ecommerce\Category;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\BusinessType;
use Illuminate\Support\Facades\Storage;

class BusinessTypeController extends Controller
{
    public function index()
    {
        try {
            $businessTypes = BusinessType::all();
            return response()->json(['status' => true, 'data' => $businessTypes], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error fetching Business Types.'], 500);
        }
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);
        try {
            $businessType = new BusinessType($validated);
            $businessType->save();
            return response()->json(['status' => true, 'message' => 'Business Type created successfully.', 'data' => $businessType], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error creating Business Type.'], 500);
        }
    }
    public function show($id)
    {
        try {
            $businessType = BusinessType::findOrFail($id);
            return response()->json(['status' => true, 'data' => $businessType], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Business Type not found.'], 404);
        }
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);
        try {
            $businessType = BusinessType::findOrFail($id);
            $businessType->fill($validated);
            $businessType->save();
            return response()->json(['status' => true, 'message' => 'Business Type updated successfully.', 'data' => $businessType], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error updating Business Type.'], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $businessType = BusinessType::findOrFail($id);
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
