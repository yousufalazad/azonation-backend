<?php

namespace App\Http\Controllers;

use App\Models\OrgAddress;
use Illuminate\Http\Request;

class OrgAddressController extends Controller
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
        $orgAddress = OrgAddress::find($id);
        return response()->json([
            'status' => true,
            'data' => $orgAddress
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrgAddress $orgAddress)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $orgAddress = OrgAddress::find($id);
        $orgAddress->update($request->all());
        return response()->json([
            'status' => true,
            'data' => $orgAddress
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrgAddress $orgAddress)
    {
        //
    }
}
