<?php
namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        return Role::with('permissions')->get();
    }

    // public function permissions()
    // {
    //     return Permission::all();
    // }
     public function permissions(Request $request, $roleId)
    {
        $role = Role::findOrFail($roleId);

        $permissions = $request->input('permissions', []);
        $role->syncPermissions($permissions); // sync removes old, adds new

        return response()->json([
            'status' => true,
            'message' => 'Role permissions updated successfully',
            'permissions' => $role->permissions()->pluck('name')
        ]);
    }

    public function store(Request $request)
    {
        $role = Role::create([
            'org_type_user_id' => $request->org_type_user_id??1,
            'name' => $request->name
        ]);

        if($request->permissions){
            $role->syncPermissions($request->permissions);
        }

        return response()->json($role->load('permissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $role->update(['name'=>$request->name, 'org_type_user_id' => $request->org_type_user_id,]);

        $role->syncPermissions($request->permissions);

        return response()->json($role->load('permissions'));
    }

    public function destroy($id)
    {
        Role::findOrFail($id)->delete();
        return response()->json(['success'=>true]);
    }
}