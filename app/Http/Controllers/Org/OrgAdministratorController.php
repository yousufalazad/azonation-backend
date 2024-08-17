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
    

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // 'org_id' => 'required',
            // 'individual_id' => 'required',
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

    /**
     * Display the specified resource.
     */
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrgAdministrator $orgAdministrator)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $orgAdministrator = OrgAdministrator::find($id);
        $orgAdministrator->update($request->all());
        return response()->json([
            'status' => true,
            'data' => $orgAdministrator
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrgAdministrator $orgAdministrator)
    {
        //
    }
}
