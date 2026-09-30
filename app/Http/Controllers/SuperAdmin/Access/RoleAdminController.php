<?php

namespace App\Http\Controllers\SuperAdmin\Access;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles and permissions are shared by every organisation. The Super Admin decides which
 * permissions each role has; organisations then give roles to their members.
 * Roles named after a plan (free_trial_org, starter...) give an organisation account its
 * plan's features: they are marked so they are not handed to members by mistake.
 */
class RoleAdminController extends Controller
{
    public function index()
    {
        $planSlugs = DB::table('management_packages')->pluck('slug')->all();
        $people = DB::table('model_has_roles')->select('role_id', DB::raw('count(distinct model_id) as n'))
            ->groupBy('role_id')->pluck('n', 'role_id');

        $roles = Role::with('permissions:id,name')->orderBy('name')->get()->map(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'is_plan' => in_array($r->name, $planSlugs, true),
            'permissions' => $r->permissions->pluck('name')->sort()->values(),
            'people' => (int) ($people[$r->id] ?? 0),
        ]);

        $usage = DB::table('role_has_permissions')->select('permission_id', DB::raw('count(*) as n'))
            ->groupBy('permission_id')->pluck('n', 'permission_id');
        $permissions = Permission::orderBy('name')->get(['id', 'name'])
            ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'roles' => (int) ($usage[$p->id] ?? 0)]);

        return response()->json(['status' => true, 'data' => ['roles' => $roles, 'permissions' => $permissions]]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions']);
        $this->refresh();

        return response()->json(['status' => true, 'data' => ['id' => $role->id]], 201);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $data = $this->validated($request, $role->id);
        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions']);
        $this->refresh();

        return response()->json(['status' => true]);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        abort_if(DB::table('management_packages')->where('slug', $role->name)->exists(), 422, 'This role belongs to a plan and cannot be deleted.');
        DB::table('model_has_roles')->where('role_id', $role->id)->delete();
        $role->delete();
        $this->refresh();

        return response()->json(['status' => true]);
    }

    public function storePermission(Request $request)
    {
        $data = $request->validate([
            // module.action, for example meeting.read
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_-]+(\.[a-z0-9_-]+)+$/', Rule::unique('permissions', 'name')->where('guard_name', 'web')],
        ]);
        Permission::create(['name' => $data['name'], 'guard_name' => 'web']);
        $this->refresh();

        return response()->json(['status' => true], 201);
    }

    public function destroyPermission($id)
    {
        Permission::findOrFail($id)->delete();
        $this->refresh();

        return response()->json(['status' => true]);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($ignoreId)],
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);
        $data['name'] = trim($data['name']);
        $data['permissions'] = $data['permissions'] ?? [];

        return $data;
    }

    private function refresh(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
