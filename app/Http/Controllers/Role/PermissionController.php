<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    // public function index()
    // {
    //     return Permission::all();
    // }

    public function index()
    {
        return Permission::orderBy('id', 'desc')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions'
        ]);

        $permission = Permission::create([
            'name' => $request->name
        ]);

        return response()->json($permission);
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $permission->update([
            'name' => $request->name
        ]);

        return response()->json($permission);
    }

    public function destroy($id)
    {
        Permission::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
