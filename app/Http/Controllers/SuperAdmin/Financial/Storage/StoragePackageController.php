<?php
namespace App\Http\Controllers\SuperAdmin\Financial\Storage;
use App\Http\Controllers\Controller;

use App\Models\StoragePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StoragePackageController extends Controller
{
    public function index()
    {
        try {
            $packages = StoragePackage::where('is_active', true)->get();
            return response()->json([
                'status' => true,
                'data' => $packages,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching packages: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching packages.',
            ], 500);
        }
    }
    public function create() {}
    public function store(Request $request) {}
    public function show(StoragePackage $storagePackage) {}
    public function edit(StoragePackage $storagePackage) {}
    public function update(Request $request, StoragePackage $storagePackage) {}
    public function destroy(StoragePackage $storagePackage) {}
}
