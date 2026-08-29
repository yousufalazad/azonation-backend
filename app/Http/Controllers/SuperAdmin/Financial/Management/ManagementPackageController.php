<?php
namespace App\Http\Controllers\SuperAdmin\Financial\Management;
use App\Http\Controllers\Controller;

use App\Models\ManagementPackage;

use Illuminate\Http\Request;

class ManagementPackageController extends Controller
{
    
     public function index() {
        try {
            $managementPackages = ManagementPackage::where('is_active', 1)->get();
            return response()->json([
                'status' => true,
                'managementPackages' => $managementPackages,
                'message' => 'Management packages fetched successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching management packages',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function create() {}
    public function store(Request $request) {}
    public function show(ManagementPackage $managementPackage) {}
    public function edit(ManagementPackage $managementPackage) {}
    public function update(Request $request, ManagementPackage $managementPackage) {}
    public function destroy(ManagementPackage $managementPackage) {}
}
