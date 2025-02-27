<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show($id)
    {
        $superAdminUserData = SuperAdmin::where('user_id', $id)->first();
        if ($superAdminUserData) {
            return response()->json(['status' => true, 'data' => $superAdminUserData]);
        } else {
            return response()->json(['status' => false, 'message' => 'SuperAdmin not found']);
        }
    }
    public function edit(SuperAdmin $superAdmin) {}
    public function update(Request $request, SuperAdmin $superAdmin) {}
    public function destroy(SuperAdmin $superAdmin) {}
}
