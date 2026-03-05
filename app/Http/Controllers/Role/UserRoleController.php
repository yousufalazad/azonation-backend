<?php

// app/Http/Controllers/Api/UserRoleController.php
namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    public function updateRolePermissions(Request $request, $roleId)
    {
        $role = Role::findOrFail($roleId);
        $permissions = $request->input('permissions', []);

        // Only sync permissions that exist in DB
        $validPermissions = \Spatie\Permission\Models\Permission::whereIn('name', $permissions)->pluck('name');
        $role->syncPermissions($validPermissions);

        return response()->json([
            'status' => true,
            'message' => 'Role permissions updated successfully',
            'permissions' => $role->permissions()->pluck('name')
        ]);
    }

    // Get all users with roles
    public function getUsers()
    {
        $type = request()->query('type')??'individual'; // optional filter by org_type_user_id
        $query = User::with('roles');

        if ($type) {
            $query->where('type', $type);
        }

        $users = $query->get(); // include roles
        return response()->json($users);
    }

    // Assign roles to a user
    public function assignRoles(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $roles = $request->input('roles', []); // array of role names
        $validRoles = Role::whereIn('name', $roles)->pluck('name'); // prevent invalid roles

        $user->syncRoles($validRoles); // remove old roles, add new ones

        return response()->json([
            'status' => true,
            'message' => 'Roles assigned successfully',
            'roles' => $user->roles->pluck('name')
        ]);
    }

    public function assign(Request $request, $userId)
    {
        $request->validate([
            'roles' => 'array'
        ]);

        $user = User::findOrFail($userId);
        $user->syncRoles($request->roles);

        return response()->json(['message' => 'Roles assigned']);
    }
}
