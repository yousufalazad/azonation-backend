<?php
namespace App\Http\Controllers\SuperAdmin\Settings;
use App\Http\Controllers\Controller;

use App\Models\PrivacySetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PrivacySetupController extends Controller
{
    public function index()
    {
        $privacySetups = PrivacySetup::where('is_active', 1)
            ->orderBy('id', 'asc')
            ->get();
        return response()->json([
            'status' => true,
            'data' => $privacySetups
        ]);
    }
    public function getAllPrivacySetupForSuperAdmin()
    {
        $privacySetups = PrivacySetup::orderBy('id', 'asc')
            ->get();
        return response()->json([
            'status' => true,
            'data' => $privacySetups
        ]);
    }
    public function create() {}
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            Log::info('Privacy Setup data: ', ['name' => $request->name, 'description' => $request->description]);
            $privacySetup = PrivacySetup::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $privacySetup, 'message' => 'Privacy Setup created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Privacy Setup: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create Privacy Setup.'], 500);
        }
    }
    public function show(PrivacySetup $privacySetup) {}
    public function edit(PrivacySetup $privacySetup) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $privacySetup = PrivacySetup::find($id);
        if (!$privacySetup) {
            return response()->json(['status' => false, 'message' => 'Privacy Setup not found.'], 404);
        }
        $privacySetup->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'data' => $privacySetup, 'message' => 'Privacy Setup updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $privacySetup = PrivacySetup::find($id);
        if (!$privacySetup) {
            return response()->json(['status' => false, 'message' => 'Privacy Setup not found.'], 404);
        }
        $privacySetup->delete();
        return response()->json(['status' => true, 'message' => 'Privacy Setup deleted successfully.'], 200);
    }
}
