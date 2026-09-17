<?php

// app/Http/Controllers/Api/UserRoleController.php
namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\OrgMemberRoleTitle;
use Illuminate\Support\Facades\DB;
use App\Models\OrgMember;
use Illuminate\Support\Facades\Storage;

class UserRoleController extends Controller
{
    // Get all users with roles
    public function getUsers()
    {
        // optional filter by org_type_user_id
        $type = request()->query('type') ?? 'individual';
        $query = User::with('roles');
        if ($type) {
            $query->where('type', $type);
        }
        $users = $query->get();
        return response()->json($users);
    }


    public function X_getOrgMemberList($userId)
    {
        $members = User::with('roles')
            ->join('org_members', 'users.id', '=', 'org_members.individual_type_user_id')
            ->where('org_members.org_type_user_id', $userId)
            // ->where('org_members.is_active', 1)
            ->select('users.*')
            ->get();

        return response()->json($members);
    }

    public function XX_getOrgMemberList($userId)
    {
        $members = User::with('roles')
            ->whereHas('orgMembers', function ($q) use ($userId) {
                $q->where('org_type_user_id', $userId)
                    ->where('is_active', 1);
            })
            ->select('id', 'first_name', 'last_name')
            ->get();
        return response()->json($members);
    }
    public function XXX_getOrgMemberList($orgId)
    {

        $user = User::find(4);

        dd(
            $user->roles,
            $user->getRoleNames(),
            DB::table('model_has_roles')->where('model_id', 4)->get()
        );
        exit;
        $members = User::with('roles:id,name')
            ->whereHas('orgMembers', function ($q) use ($orgId) {
                $q->where('org_type_user_id', $orgId)
                    ->where('is_active', 1);
            })
            ->get(['id', 'first_name', 'last_name']);

        return response()->json($members);
    }

    public function getOrgMemberList($orgId)
    {
        $members = User::whereHas('orgMembers', function ($q) use ($orgId) {
            $q->where('org_type_user_id', $orgId)
                ->where('is_active', 1);
        })
            ->get(['id', 'first_name', 'last_name'])
            ->map(function ($user) use ($orgId) {
                $user->roles = $user->rolesByOrg($orgId)->get(['id', 'name']);
                return $user;
            });

        return response()->json($members);
    }
    // Update role permissions
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

    // Assign roles to a user
    public function assignRoles(Request $request, $userId)
    {
        // dd($request->all());exit;
        $request->validate([
            'roles' => 'nullable|array',
            'org_type_user_id' => 'required|integer',
            'org_role_title_id' => 'nullable|integer'
        ]);

        DB::beginTransaction();

        try {
            $user = User::findOrFail($userId);

            $roles = collect($request->roles ?? [])
                ->map(fn($r) => trim($r))
                ->toArray();

            $roleModels = Role::whereIn('name', $roles)
                ->where('guard_name', 'web')
                ->get();

            // Sync roles manually with org_type_user_id
            $existingRoleIds = DB::table('model_has_roles')
                ->where('model_type', User::class)
                ->where('model_id', $user->id)
                ->pluck('role_id')
                ->toArray();

            $newRoleIds = $roleModels->pluck('id')->toArray();

            // Remove old roles not in new
            $toDelete = array_diff($existingRoleIds, $newRoleIds);
            if ($toDelete) {
                DB::table('model_has_roles')
                    ->whereIn('role_id', $toDelete)
                    ->where('model_type', User::class)
                    ->where('model_id', $user->id)
                    ->delete();
            }

            // Insert/update new roles with org_type_user_id
            foreach ($roleModels as $role) {
                DB::table('model_has_roles')->updateOrInsert(
                    [
                        'role_id' => $role->id,
                        'model_type' => User::class,
                        'model_id' => $user->id,
                    ],
                    [
                        'org_type_user_id' => $request->org_type_user_id
                    ]
                );
            }

            // Save org member title
            if ($request->org_role_title_id) {
                OrgMemberRoleTitle::updateOrCreate(
                    [
                        'org_type_user_id' => $request->org_type_user_id,
                        'individual_type_user_id' => $userId,
                    ],
                    [
                        'org_role_title_id' => $request->org_role_title_id
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Roles assigned successfully',
                'roles' => $roleModels->pluck('name')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
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
