<?php
namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    // Roles an organisation can give its members. Plan roles (named after a plan's slug) give an
    // organisation account its plan features and are only listed for Super Admins.
    public function index()
    {
        $roles = Role::with('permissions:id,name')->orderBy('name')->get();
        if (request()->user()?->type !== 'superadmin') {
            $planSlugs = DB::table('management_packages')->pluck('slug')->all();
            $roles = $roles->reject(fn ($r) => in_array($r->name, $planSlugs, true))->values();
        }
        return $roles;
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