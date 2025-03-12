<?php
namespace App\Http\Controllers\Org\Meeting;
use App\Http\Controllers\Controller;

use App\Models\MeetingConductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MeetingConductTypeController extends Controller
{
    public function index()
    {
        $countries = MeetingConductType::all();
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
            $meetingConductType = MeetingConductType::create([
                'name' => $request->name,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $meetingConductType, 'message' => 'MeetingConductType created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating MeetingConductType: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create MeetingConductType.'], 500);
        }
    }
    public function show(MeetingConductType $meetingConductType) {}
    public function edit(MeetingConductType $meetingConductType) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $meetingConductType = MeetingConductType::find($id);
        if (!$meetingConductType) {
            return response()->json(['status' => false, 'message' => 'MeetingConductType not found.'], 404);
        }
        $meetingConductType->update([
            'name' => $request->name,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'data' => $meetingConductType, 'message' => 'MeetingConductType updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $meetingConductType = MeetingConductType::find($id);
        if (!$meetingConductType) {
            return response()->json(['status' => false, 'message' => 'MeetingConductType not found.'], 404);
        }
        $meetingConductType->delete();
        return response()->json(['status' => true, 'message' => 'MeetingConductType deleted successfully.'], 200);
    }
}
