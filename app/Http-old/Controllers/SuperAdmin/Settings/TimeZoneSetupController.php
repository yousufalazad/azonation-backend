<?php
namespace App\Http\Controllers\SuperAdmin\Settings;
use App\Http\Controllers\Controller;
use App\Models\TimeZoneSetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TimeZoneSetupController extends Controller
{
    public function index()
    {
        $designation = TimeZoneSetup::all();
        return response()->json(['status' => true, 'data' => $designation], 200);
    }
    public function create() {}
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'time_zone' => 'required|string|max:255',
            'offset' => 'required',
            'description' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            Log::info('TimeZoneSetup data: ', ['time_zone' => $request->time_zone, 'offset' => $request->offset, 'description' => $request->description, 'is_active' => $request->is_active]);
            $designation = TimeZoneSetup::create([
                'time_zone' => $request->time_zone,
                'offset' => $request->offset,
                'description' => $request->description,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $designation, 'message' => 'TimeZoneSetup created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating TimeZoneSetup: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create TimeZoneSetup.'], 500);
        }
    }
    public function show(TimeZoneSetup $timeZoneSetup) {}
    public function edit(TimeZoneSetup $timeZoneSetup) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'time_zone' => 'required|string|max:255',
            'offset' => 'required',
            'description' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $designation = TimeZoneSetup::find($id);
        if (!$designation) {
            return response()->json(['status' => false, 'message' => 'TimeZoneSetup not found.'], 404);
        }
        $designation->update([
            'time_zone' => $request->time_zone,
            'offset' => $request->offset,
            'description' => $request->description,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'data' => $designation, 'message' => 'TimeZoneSetup updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $designation = TimeZoneSetup::find($id);
        if (!$designation) {
            return response()->json(['status' => false, 'message' => 'TimeZoneSetup not found.'], 404);
        }
        $designation->delete();
        return response()->json(['status' => true, 'message' => 'TimeZoneSetup deleted successfully.'], 200);
    }
}
