<?php
namespace App\Http\Controllers\SuperAdmin\Settings;
use App\Http\Controllers\Controller;

use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RegionController extends Controller
{
    public function index()
    {
        $regions = Region::all();
        return response()->json(['status' => true, 'data' => $regions], 200);
    }
    public function create() {}
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'title' => 'required|string',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            Log::info('Region data: ', ['name' => $request->name, 'title' => $request->title]);
            $region = Region::create([
                'name' => $request->name,
                'title' => $request->title,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $region, 'message' => 'Region created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Region: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create Region.'], 500);
        }
    }
    public function show(Region $region) {}
    public function edit(Region $region) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'title' => 'required|string',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $region = Region::find($id);
        if (!$region) {
            return response()->json(['status' => false, 'message' => 'Region not found.'], 404);
        }
        $region->update([
            'name' => $request->name,
            'title' => $request->title,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'data' => $region, 'message' => 'Region updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $region = Region::find($id);
        if (!$region) {
            return response()->json(['status' => false, 'message' => 'Region not found.'], 404);
        }
        $region->delete();
        return response()->json(['status' => true, 'message' => 'Region deleted successfully.'], 200);
    }
}
