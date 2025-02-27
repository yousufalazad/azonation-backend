<?php
namespace App\Http\Controllers\Org\Asset;
use App\Http\Controllers\Controller;

use App\Models\AssetLifecycleStatus;
use Illuminate\Http\Request;

class AssetLifecycleStatusController extends Controller
{
    public function index()
    {
        $assetLifecycleStatus = AssetLifecycleStatus::where('is_active', 1)
            ->orderBy('id', 'asc')
            ->get();
        return response()->json([
            'status' => true,
            'data' => $assetLifecycleStatus
        ]);
    }
    public function create() {}
    public function store(Request $request) {}
    public function show(AssetLifecycleStatus $assetLifecycleStatus) {}
    public function edit(AssetLifecycleStatus $assetLifecycleStatus) {}
    public function update(Request $request, AssetLifecycleStatus $assetLifecycleStatus) {}
    public function destroy(AssetLifecycleStatus $assetLifecycleStatus) {}
}
