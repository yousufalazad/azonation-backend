<?php
namespace App\Http\Controllers\Org\Asset;
use App\Http\Controllers\Controller;

use App\Models\Asset;
use App\Models\AssetFile;
use App\Models\AssetImage;
use App\Models\AssetAssignmentLog;
use App\Models\AssetLifecycleStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $user_id = $request->user()->id;
        $assets = DB::table('assets as a')
            ->select(
                'a.id as id',
                'a.user_id as user_id',
                'a.name as name',
                'a.description as description',
                'a.is_long_term as is_long_term',
                'a.quantity as quantity',
                'a.value_amount as value_amount',
                'a.inkind_value as inkind_value',
                'a.is_tangible as is_tangible',
                'ps.name as privacy_setup_name',
                'a.is_active as is_active',
                'u.name as responsible_user_name',
                'aal.assignment_start_date as assignment_start_date',
                'aal.assignment_end_date as assignment_end_date',
                'als.name as asset_lifecycle_statuses_name',
                'aal.note as note'
            )
            ->join('asset_assignment_logs as aal', 'a.id', '=', 'aal.asset_id')
            ->join('privacy_setups as ps', 'a.privacy_setup_id', '=', 'ps.id')
            ->join('users as u', 'aal.responsible_user_id', '=', 'u.id')
            ->join('asset_lifecycle_statuses as als', 'aal.asset_lifecycle_statuses_id', '=', 'als.id')
            ->where('a.user_id', '=', $user_id)
            ->get();
        return response()->json(['status' => true, 'data' => $assets], 200);
    }
    public function getAssetDetails($assetId)
    {
        $asset = DB::table('assets as a')
            ->select(
                'a.id as id',
                'a.user_id as user_id',
                'a.name as name',
                'a.description as description',
                'a.is_long_term as is_long_term',
                'a.quantity as quantity',
                'a.value_amount as value_amount',
                'a.inkind_value as inkind_value',
                'a.is_tangible as is_tangible',
                'a.privacy_setup_id as privacy_setup_id',
                'ps.name as privacy_setup_name',
                'a.is_active as is_active',
                'u.name as responsible_user_name',
                'aal.responsible_user_id as responsible_user_id',
                'aal.asset_lifecycle_statuses_id as asset_lifecycle_statuses_id',
                'aal.assignment_start_date as assignment_start_date',
                'aal.assignment_end_date as assignment_end_date',
                'als.name as asset_lifecycle_statuses_name',
                'aal.note as note'
            )
            ->join('asset_assignment_logs as aal', 'a.id', '=', 'aal.asset_id')
            ->join('privacy_setups as ps', 'a.privacy_setup_id', '=', 'ps.id')
            ->join('users as u', 'aal.responsible_user_id', '=', 'u.id')
            ->join('asset_lifecycle_statuses as als', 'aal.asset_lifecycle_statuses_id', '=', 'als.id')
            ->where('a.id', '=', $assetId)
            ->first();
        if (!$asset) {
            return response()->json(['status' => false, 'message' => 'Asset not found'], 404);
        }
        $documents = AssetFile::where('asset_id', $assetId)->get();
        $images = AssetImage::where('asset_id', $assetId)->get();
        $images = $images->map(function ($image) {
            $image->image_url = $image->file_path
                ? url(Storage::url($image->file_path))
                : null;
            return $image;
        });
        $documents = $documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });
        $assetDetails = (array) $asset;
        $assetDetails['documents'] = $documents;
        $assetDetails['images'] = $images;
        return response()->json(['status' => true, 'data' => $assetDetails], 200);
    }
    public function show($id)
    {
        $asset = Asset::with(['assignmentLogs', 'documents', 'images'])->find($id);
        if (!$asset) {
            return response()->json(['message' => 'Asset not found.'], 404);
        }
        $asset->images = $asset->images->map(function ($image) {
            $image->image_url = $image->file_path
                ? url(Storage::url($image->file_path))
                : null;
            return $image;
        });
        $asset->documents = $asset->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });
        return response()->json($asset);
    }
    
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
        DB::beginTransaction();
        try {
            $asset = Asset::create($validated);
            AssetAssignmentLog::create([
                'asset_id' => $asset->id,
                'responsible_user_id' => $validated['responsible_user_id'],
                'assignment_start_date' => $validated['assignment_start_date'],
                'assignment_end_date' => $validated['assignment_end_date'],
                'asset_lifecycle_statuses_id' => $validated['asset_lifecycle_statuses_id'],
                'note' => $validated['note'],
                'is_active' => $validated['is_active']
            ]);
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/asset/file',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    AssetFile::create([
                        'asset_id' => $asset->id,
                        'file_path' => $documentPath,
                        'file_name' => $document->getClientOriginalName(),
                        'mime_type' => $document->getClientMimeType(),
                        'file_size' => $document->getSize(),
                        'is_public' => true,
                        'is_active' => true,
                    ]);
                }
            }
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->storeAs(
                        'org/asset/image',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    AssetImage::create([
                        'asset_id' => $asset->id,
                        'file_path' => $imagePath,
                        'file_name' => $image->getClientOriginalName(),
                        'mime_type' => $image->getClientMimeType(),
                        'file_size' => $image->getSize(),
                        'is_public' => true,
                        'is_active' => true,
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Asset created successfully.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.'], 500);
        }
    }
    
    public function update(Request $request, $id)
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
        DB::beginTransaction();
        try {
            $asset = Asset::findOrFail($id);
            $asset->update($validated);
            $assetAssignmentLog = AssetAssignmentLog::where('asset_id', $asset->id)->first();
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
                    'asset_id' => $asset->id,
                    'responsible_user_id' => $validated['responsible_user_id'],
                    'assignment_start_date' => $validated['assignment_start_date'],
                    'assignment_end_date' => $validated['assignment_end_date'],
                    'asset_lifecycle_statuses_id' => $validated['asset_lifecycle_statuses_id'],
                    'note' => $validated['note'],
                    'is_active' => $validated['is_active']
                ]);
            }
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/asset/file',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    AssetFile::create([
                        'asset_id' => $asset->id,
                        'file_path' => $documentPath,
                        'file_name' => $document->getClientOriginalName(),
                        'mime_type' => $document->getClientMimeType(),
                        'file_size' => $document->getSize(),
                        'is_public' => true,
                        'is_active' => true,
                    ]);
                }
            }
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->storeAs(
                        'org/asset/image',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    AssetImage::create([
                        'asset_id' => $asset->id,
                        'file_path' => $imagePath,
                        'file_name' => $image->getClientOriginalName(),
                        'mime_type' => $image->getClientMimeType(),
                        'file_size' => $image->getSize(),
                        'is_public' => true,
                        'is_active' => true,
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Asset updated successfully.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.'], 500);
        }
    }
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $asset = Asset::findOrFail($id);
            AssetAssignmentLog::where('asset_id', $asset->id)->delete();
            $asset->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Asset deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.'], 500);
        }
    }
}
