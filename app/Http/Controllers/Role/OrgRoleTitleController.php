<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Models\OrgRoleTitle;
use Illuminate\Http\Request;

class OrgRoleTitleController extends Controller
{
    /* ================= LIST ================= */
    public function index(Request $request)
    {
        $orgId = $request->org_type_user_id;
        $condition = [];
        if ($orgId && $orgId !== 'null') {
            $condition[] = ['org_type_user_id', $orgId];
        }
        $titles = OrgRoleTitle::where($condition)
            ->orderBy('name')
            ->get();
        return $titles;
    }

    /* ================= STORE ================= */
    public function store(Request $request)
    {
        $request->validate([
            'org_type_user_id' => 'required|integer',
            'name' => 'required|string|max:255'
        ]);

        $title = OrgRoleTitle::create([
            'org_type_user_id' => $request->org_type_user_id,
            'name' => $request->name
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Role title created successfully',
            'data' => $title
        ]);
    }

    /* ================= SHOW ================= */
    public function show($id)
    {
        $title = OrgRoleTitle::findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $title
        ]);
    }

    /* ================= UPDATE ================= */
    public function update(Request $request, $id)
    {
        $title = OrgRoleTitle::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $title->update([
            'name' => $request->name,
            'org_type_user_id' => $request->org_type_user_id,

        ]);

        return response()->json([
            'status' => true,
            'message' => 'Role title updated successfully',
            'data' => $title
        ]);
    }

    /* ================= DELETE ================= */
    public function destroy($id)
    {
        $title = OrgRoleTitle::findOrFail($id);

        $title->delete();

        return response()->json([
            'status' => true,
            'message' => 'Role title deleted successfully'
        ]);
    }
}
