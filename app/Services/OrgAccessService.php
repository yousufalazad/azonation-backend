<?php

namespace App\Services;

use App\Models\User;
use App\Models\OrgRoleTitle;
use App\Models\OrgMemberRoleTitle;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class OrgAccessService
{
    // org-wise roles + permissions
    public function getOrgAccess($user): array
    {
        $orgs = DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->where('model_id', $user->id)
            ->whereNotNull('org_type_user_id')
            ->distinct()
            ->pluck('org_type_user_id');

        $data = [];

        foreach ($orgs as $orgId) {
            $roles = DB::table('roles')
                ->join('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_type', User::class)
                ->where('model_has_roles.model_id', $user->id)
                ->where('model_has_roles.org_type_user_id', $orgId)
                ->pluck('roles.name')
                ->unique()
                ->values();

            $data[] = [
                'org_type_user_id' => $orgId,
                'roles'            => $roles,
                'permissions'      => $this->getPermissionsByOrg($user->id, $orgId),
            ];
        }

        return $data;
    }

    // permission merge (multi role → single list)
    public function getPermissionsByOrg($userId, $orgId)
    {
        return DB::table('model_has_roles')
            ->join('role_has_permissions', 'model_has_roles.role_id', '=', 'role_has_permissions.role_id')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('model_has_roles.model_type', User::class)
            ->where('model_has_roles.model_id', $userId)
            ->where('model_has_roles.org_type_user_id', $orgId)
            ->pluck('permissions.name')
            ->unique()
            ->values();
    }

    public function assignUserRoles($userId, array $roles, $orgTypeUserId, $orgRoleTitle = null, $isNewUser = false): void
    {
        $user = User::findOrFail($userId);

        $roleModels = Role::whereIn('name', array_filter($roles))
            ->where('guard_name', 'web')
            ->get();

        foreach ($roleModels as $role) {
            DB::table('model_has_roles')->updateOrInsert(
                ['role_id' => $role->id, 'model_type' => User::class, 'model_id' => $user->id],
                ['org_type_user_id' => $orgTypeUserId]
            );
        }

        $orgRoleTitleData = null;
        if ($orgRoleTitle) {
            $orgRoleTitleData = OrgRoleTitle::updateOrCreate([
                'org_type_user_id' => $orgTypeUserId,
                'name'             => $orgRoleTitle,
            ]);
        }

        if ($orgRoleTitleData) {
            OrgMemberRoleTitle::updateOrCreate(
                ['org_type_user_id' => $orgTypeUserId, 'individual_type_user_id' => $userId],
                ['org_role_title_id' => $orgRoleTitleData->id]
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}