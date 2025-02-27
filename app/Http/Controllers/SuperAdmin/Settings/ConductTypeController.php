<?php
namespace App\Http\Controllers\SuperAdmin\Settings;
use App\Http\Controllers\Controller;

use App\Models\ConductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ConductTypeController extends Controller
{
    public function index()
    {
        $countries = ConductType::all();
        return response()->json(['status' => true, 'data' => $countries], 200);
    }
    public function create() {}
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            Log::info('Meeting Conduct Type data: ', ['name' => $request->name, 'status' => $request->status]);
            $conductType = ConductType::create([
                'name' => $request->name,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $conductType, 'message' => 'ConductType created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating ConductType: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create ConductType.'], 500);
        }
    }
    public function show(ConductType $conductType) {}
    public function edit(ConductType $conductType) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $conductType = ConductType::find($id);
        if (!$conductType) {
            return response()->json(['status' => false, 'message' => 'ConductType not found.'], 404);
        }
        $conductType->update([
            'name' => $request->name,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'data' => $conductType, 'message' => 'ConductType updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $conductType = ConductType::find($id);
        if (!$conductType) {
            return response()->json(['status' => false, 'message' => 'ConductType not found.'], 404);
        }
        $conductType->delete();
        return response()->json(['status' => true, 'message' => 'ConductType deleted successfully.'], 200);
    }
}
