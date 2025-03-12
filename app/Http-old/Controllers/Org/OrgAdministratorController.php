<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\OrgAdministrator;
use Illuminate\Http\Request;
use App\Models\Individual;

class OrgAdministratorController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = Individual::where('id', 'like', "%{$query}%")
            ->orWhere('user_id', 'like', "%{$query}%")
            ->orWhere('azon_id', 'like', "%{$query}%")
            ->orWhere('full_name', 'like', "%{$query}%")
            ->get();
        return response()->json([
            'status' => true,
            'data' => $results
        ]);
    }
    public function index() {}
    public function create() {}
    public function store(Request $request)
    {
        $validated = $request->validate([
            'org_id' => 'required|exists:organisations,id',
            'individual_id' => 'required|exists:individuals,id',
        ]);
        OrgAdministrator::create([
            'org_id' => $validated['org_id'],
            'individual_id' => $validated['individual_id'],
            'status' => 1
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Administrator added successfully'
        ]);
    }
    public function show($orgId)
    {
        $orgAdministrator = OrgAdministrator::where('org_id', $orgId)
            ->with('individual')
            ->get();
        return response()->json([
            'status' => true,
            'data' => $orgAdministrator
        ]);
    }
    public function edit(OrgAdministrator $orgAdministrator) {}
    public function update(Request $request, $id)
    {
        $orgAdministrator = OrgAdministrator::find($id);
        $orgAdministrator->update($request->all());
        return response()->json([
            'status' => true,
            'data' => $orgAdministrator
        ]);
    }
    public function destroy(OrgAdministrator $orgAdministrator) {}
}
