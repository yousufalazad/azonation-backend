<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignmentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{

    /**
     * Display a listing of assets.
     */
    public function index()
    {
        $assets = Asset::with('assignmentLogs')->get();
        return response()->json(['status' => true, 'data' => $assets], 200);
    }
    /**
     * Store a new asset and assignment log.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_long_term' => 'required|boolean',
            'quantity' => 'required|integer',
            'value_amount' => 'required|numeric',
            'inkind_value' => 'nullable|numeric',
            'is_tangible' => 'required|boolean',
            'privacy_setup_id' => 'required|integer',
            'is_active' => 'required|boolean',
            'responsible_user_id' => 'required|integer',
            'assignment_start_date' => 'required|date',
            'assignment_end_date' => 'nullable|date',
            'asset_lifecycle_statuses_id' => 'required|integer',
            'note' => 'nullable|string',
        ]);

        // DB::beginTransaction();

        // try {
            // Create Asset
            $asset = Asset::create($validated);

            // Create Asset Assignment Log
            AssetAssignmentLog::create([
                'asset_id' => $asset->id,
                'responsible_user_id' => $validated['responsible_user_id'],
                'assignment_start_date' => $validated['assignment_start_date'],
                'assignment_end_date' => $validated['assignment_end_date'],
                'asset_lifecycle_statuses_id' => $validated['asset_lifecycle_statuses_id'],
                'note' => $validated['note'],
                'is_active' => $validated['is_active']
            ]);

            //DB::commit();
            return response()->json(['message' => 'Asset created successfully'], 201);
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     return response()->json(['message' => 'An error occurred. Please try again.'], 500);
        // }
    }

    /**
     * Show a single asset with its assignment log.
     */
    public function show($id)
    {
        $asset = Asset::with('assignmentLogs')->find($id);

        if (!$asset) {
            return response()->json(['message' => 'Asset not found.'], 404);
        }

        return response()->json($asset);
    }

    /**
     * Update an asset and its assignment log.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_long_term' => 'required|boolean',
            'quantity' => 'required|integer',
            'value_amount' => 'required|numeric',
            'inkind_value' => 'nullable|numeric',
            'is_tangible' => 'required|boolean',
            'privacy_setup_id' => 'required|integer',
            'is_active' => 'required|boolean',
            'responsible_user_id' => 'required|integer',
            'assignment_start_date' => 'required|date',
            'assignment_end_date' => 'nullable|date',
            'asset_lifecycle_statuses_id' => 'required|integer',
            'note' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $asset = Asset::find($id);

            if (!$asset) {
                return response()->json(['message' => 'Asset not found.'], 404);
            }

            // Update Asset
            $asset->update($validated);

            // Update or create Asset Assignment Log
            $assetAssignmentLog = AssetAssignmentLog::where('asset_id', $id)->first();
            if ($assetAssignmentLog) {
                $assetAssignmentLog->update([
                    'responsible_user_id' => $validated['responsible_user_id'],
                    'assignment_start_date' => $validated['assignment_start_date'],
                    'assignment_end_date' => $validated['assignment_end_date'],
                    'asset_lifecycle_statuses_id' => $validated['asset_lifecycle_statuses_id'],
                    'note' => $validated['note'],
                    'is_active' => $validated['is_active']
                ]);
            } else {
                AssetAssignmentLog::create([
                    'asset_id' => $id,
                    'responsible_user_id' => $validated['responsible_user_id'],
                    'assignment_start_date' => $validated['assignment_start_date'],
                    'assignment_end_date' => $validated['assignment_end_date'],
                    'asset_lifecycle_statuses_id' => $validated['asset_lifecycle_statuses_id'],
                    'note' => $validated['note'],
                    'is_active' => $validated['is_active']
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Asset updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred. Please try again.'], 500);
        }
    }

    /**
     * Delete an asset and its assignment log.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $asset = Asset::find($id);

            if (!$asset) {
                return response()->json(['message' => 'Asset not found.'], 404);
            }

            // Delete Asset Assignment Log
            AssetAssignmentLog::where('asset_id', $id)->delete();

            // Delete Asset
            $asset->delete();

            DB::commit();
            return response()->json(['message' => 'Asset deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred. Please try again.'], 500);
        }
    }
}
