<?php

namespace App\Http\Controllers\Role;

use App\Http\Concerns\ResolvesCurrentOrg;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use App\Models\OrgMemberRoleTitle;
use Illuminate\Support\Facades\DB;
use App\Models\OrgMember;

class UserRoleController extends Controller
{
    use ResolvesCurrentOrg;

    private function isSuperAdmin(): bool
    {
        return request()->user()?->type === 'superadmin';
    }

    /**
     * The org a role change applies to: Super Admins may name any org,
     * an organisation account only itself. Everyone else: null (refuse).
     */
    private function managedOrgId(Request $request): ?int
    {
        if ($this->isSuperAdmin()) {
            return (int) $request->input('org_type_user_id') ?: null;
        }
        $user = $request->user();
        return $user?->type === 'organisation' ? (int) $user->id : null;
    }

    private function isActiveMember(int $orgId, int $userId): bool
    {
        return OrgMember::where('org_type_user_id', $orgId)
            ->where('individual_type_user_id', $userId)
            ->where('is_active', 1)
            ->exists();
    }

    // Users with roles. Super Admins see everyone; an organisation sees only
    // itself (type=organisation) or its own members (type=individual).
    public function getUsers(Request $request)
    {
        $type = $request->query('type') ?? 'individual';
        $query = User::with('roles')->where('type', $type);

        if (!$this->isSuperAdmin()) {
            $orgId = $this->currentOrgId($request);
            if (!$orgId) {
                return response()->json([]);
            }
            if ($type === 'organisation') {
                $query->where('id', $orgId);
            } else {
                $query->whereHas('orgMembers', fn ($q) => $q->where('org_type_user_id', $orgId));
            }
        }

        return response()->json($query->get());
    }

    // Active members of an organisation with their roles in that organisation
    public function getOrgMemberList(Request $request, $orgId)
    {
        $orgId = (int) $orgId;
        if (!$this->isSuperAdmin() && $orgId !== $this->currentOrgId($request)) {
            return $this->noOrgResponse();
        }

        // Each member's title in this organisation (e.g. Treasurer), shown next to their roles
        $titles = OrgMemberRoleTitle::where('org_type_user_id', $orgId)->pluck('org_role_title_id', 'individual_type_user_id');

        $members = User::whereHas('orgMembers', function ($q) use ($orgId) {
            $q->where('org_type_user_id', $orgId)
                ->where('is_active', 1);
        })
            ->get(['id', 'first_name', 'last_name', 'azon_id'])
            ->map(function ($user) use ($orgId, $titles) {
                $user->roles = $user->rolesByOrg($orgId)->get(['id', 'name']);
                $user->org_role_title_id = $titles[$user->id] ?? null;
                return $user;
            });

        return response()->json($members);
    }

    // Update which permissions a role has (Super Admin only, see routes)
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

    // Assign roles to a user within one organisation
    public function assignRoles(Request $request, $userId)
    {
        $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'string',
            'org_type_user_id' => 'required|integer',
            'org_role_title_id' => 'nullable|integer'
        ]);

        $orgId = $this->managedOrgId($request);
        if (!$orgId) {
            return $this->noOrgResponse();
        }

        $user = User::findOrFail($userId);

        // An organisation can only give roles to its own members
        if (!$this->isSuperAdmin() && !$this->isActiveMember($orgId, $user->id)) {
            return response()->json([
                'status' => false,
                'message' => 'This person is not a member of your organisation.',
            ], 422);
        }

        // The title must be one of this organisation's titles
        if ($request->org_role_title_id && !\App\Models\OrgRoleTitle::where('id', $request->org_role_title_id)
            ->where('org_type_user_id', $orgId)->exists()) {
            return response()->json(['status' => false, 'message' => 'Unknown role title.'], 422);
        }

        DB::beginTransaction();

        try {
            $roles = collect($request->roles ?? [])
                ->map(fn($r) => trim($r))
                ->toArray();

            $roleModels = Role::whereIn('name', $roles)
                ->where('guard_name', 'web')
                // Plan roles are for organisation accounts, not members
                ->when(!$this->isSuperAdmin(), fn ($q) => $q->whereNotIn('name', DB::table('management_packages')->pluck('slug')))
                ->get();

            $newRoleIds = $roleModels->pluck('id')->toArray();

            // Replace this user's roles in THIS organisation only
            // (roles in other organisations are left untouched)
            DB::table('model_has_roles')
                ->where('model_type', User::class)
                ->where('model_id', $user->id)
                ->where('org_type_user_id', $orgId)
                ->whereNotIn('role_id', $newRoleIds ?: [0])
                ->delete();

            foreach ($roleModels as $role) {
                DB::table('model_has_roles')->updateOrInsert([
                    'role_id' => $role->id,
                    'model_type' => User::class,
                    'model_id' => $user->id,
                    'org_type_user_id' => $orgId,
                ]);
            }

            // Save org member title
            if ($request->org_role_title_id) {
                OrgMemberRoleTitle::updateOrCreate(
                    [
                        'org_type_user_id' => $orgId,
                        'individual_type_user_id' => $user->id,
                    ],
                    [
                        'org_role_title_id' => $request->org_role_title_id
                    ]
                );
            }

            DB::commit();

            // Permissions are cached by Spatie; make the change visible now
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

            return response()->json([
                'status' => true,
                'message' => 'Roles assigned successfully',
                'roles' => $roleModels->pluck('name')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('assignRoles failed', ['exception' => $e]);

            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }
}
