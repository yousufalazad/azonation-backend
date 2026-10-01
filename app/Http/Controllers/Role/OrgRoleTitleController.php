<?php

namespace App\Http\Controllers\Role;

use App\Http\Concerns\ResolvesCurrentOrg;
use App\Http\Controllers\Controller;
use App\Models\OrgRoleTitle;
use Illuminate\Http\Request;

/**
 * Role titles ("Treasurer", "Secretary"...) belong to one organisation.
 * Super Admins may work with any organisation (org_type_user_id in the
 * request); everyone else only with their current organisation.
 */
class OrgRoleTitleController extends Controller
{
    use ResolvesCurrentOrg;

    private function orgFor(Request $request): ?int
    {
        if ($request->user()?->type === 'superadmin') {
            $requested = $request->input('org_type_user_id');
            return ($requested && $requested !== 'null') ? (int) $requested : null;
        }
        return $this->currentOrgId($request);
    }

    private function findOwned(Request $request, $id): OrgRoleTitle
    {
        $query = OrgRoleTitle::where('id', $id);
        if ($request->user()?->type !== 'superadmin') {
            $query->where('org_type_user_id', $this->currentOrgId($request) ?? 0);
        }
        return $query->firstOrFail();
    }

    /* ================= LIST ================= */
    public function index(Request $request)
    {
        $orgId = $this->orgFor($request);
        $isSuperAdmin = $request->user()?->type === 'superadmin';
        if (!$orgId && !$isSuperAdmin) {
            return response()->json([]);
        }

        return OrgRoleTitle::when($orgId, fn ($q) => $q->where('org_type_user_id', $orgId))
            ->orderBy('name')
            ->get();
    }

    /* ================= STORE ================= */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $orgId = $this->orgFor($request);
        if (!$orgId) {
            return $this->noOrgResponse();
        }

        $title = OrgRoleTitle::create([
            'org_type_user_id' => $orgId,
            'name' => $request->name
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Role title created successfully',
            'data' => $title
        ]);
    }

    /* ================= SHOW ================= */
    public function show(Request $request, $id)
    {
        return response()->json([
            'status' => true,
            'data' => $this->findOwned($request, $id)
        ]);
    }

    /* ================= UPDATE ================= */
    public function update(Request $request, $id)
    {
        $title = $this->findOwned($request, $id);

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        // The title stays in its organisation
        $title->update(['name' => $request->name]);

        return response()->json([
            'status' => true,
            'message' => 'Role title updated successfully',
            'data' => $title
        ]);
    }

    /* ================= DELETE ================= */
    public function destroy(Request $request, $id)
    {
        $this->findOwned($request, $id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Role title deleted successfully'
        ]);
    }
}
