<?php
namespace App\Http\Controllers\SuperAdmin\Settings;
use App\Http\Controllers\Controller;

use App\Models\AttendanceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AttendanceTypeController extends Controller
{
    public function index()
    {
        $attendanceTypes = AttendanceType::all();
        return response()->json(['status' => true, 'data' => $attendanceTypes], 200);
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
            $attendanceType = AttendanceType::create([
                'name' => $request->name,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $attendanceType, 'message' => 'AttendanceType created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating AttendanceType: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create AttendanceType.'], 500);
        }
    }
    public function show(AttendanceType $attendanceType) {}
    public function edit(AttendanceType $attendanceType) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $attendanceType = AttendanceType::find($id);
        if (!$attendanceType) {
            return response()->json(['status' => false, 'message' => 'AttendanceType not found.'], 404);
        }
        $attendanceType->update([
            'name' => $request->name,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'data' => $attendanceType, 'message' => 'AttendanceType updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $attendanceType = AttendanceType::find($id);
        if (!$attendanceType) {
            return response()->json(['status' => false, 'message' => 'AttendanceType not found.'], 404);
        }
        $attendanceType->delete();
        return response()->json(['status' => true, 'message' => 'AttendanceType deleted successfully.'], 200);
    }
}
