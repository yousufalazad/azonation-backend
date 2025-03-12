<?php
namespace App\Http\Controllers\SuperAdmin\Financial\Management;
use App\Http\Controllers\Controller;

use App\Models\ManagementPackage;
use Illuminate\Http\Request;

class ManagementPackageController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(ManagementPackage $managementPackage) {}
    public function edit(ManagementPackage $managementPackage) {}
    public function update(Request $request, ManagementPackage $managementPackage) {}
    public function destroy(ManagementPackage $managementPackage) {}
}
