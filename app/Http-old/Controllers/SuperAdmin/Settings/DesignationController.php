<?php
namespace App\Http\Controllers\SuperAdmin\Settings;
use App\Http\Controllers\Controller;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DesignationController extends Controller
{
    public function index()
    {
        $designation = Designation::all();
        return response()->json(['status' => true, 'data' => $designation], 200);
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
            Log::info('Designation data: ', ['name' => $request->name, 'status' => $request->status]);
            $designation = Designation::create([
                'name' => $request->name,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $designation, 'message' => 'Designation created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Designation: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create Designation.'], 500);
        }
    }
    public function show(Designation $designation) {}
    public function edit(Designation $designation) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $designation = Designation::find($id);
        if (!$designation) {
            return response()->json(['status' => false, 'message' => 'Designation not found.'], 404);
        }
        $designation->update([
            'name' => $request->name,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'data' => $designation, 'message' => 'Designation updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $designation = Designation::find($id);
        if (!$designation) {
            return response()->json(['status' => false, 'message' => 'Designation not found.'], 404);
        }
        $designation->delete();
        return response()->json(['status' => true, 'message' => 'Designation deleted successfully.'], 200);
    }
}
