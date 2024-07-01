<?php

namespace App\Http\Controllers;

use App\Models\SuperAdmin;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
        //
    }

    /**
     * Display the specified resource.
     */
    
    public function show($id)
    {
        $superAdminUserData = SuperAdmin::where('user_id', $id)->first();

        if ($superAdminUserData) {
            return response()->json(['status' => true, 'data' => $superAdminUserData]);
        } else {
            return response()->json(['status' => false, 'message' => 'SuperAdmin not found']);
        }
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SuperAdmin $superAdmin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SuperAdmin $superAdmin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SuperAdmin $superAdmin)
    {
        //
    }
}
