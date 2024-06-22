<?php

namespace App\Http\Controllers;

use App\Models\OrgPhoneNumber;
use Illuminate\Http\Request;

class OrgPhoneNumberController extends Controller
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
        $orgPhoneNumber = OrgPhoneNumber::find($id);
        return response()->json([
            'status' => true,
            'data' => $orgPhoneNumber
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrgPhoneNumber $orgPhoneNumber)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $orgPhoneNumber = OrgPhoneNumber::find($id);
        $orgPhoneNumber->update($request->all());
        return response()->json([
            'status' => true,
            'data' => $orgPhoneNumber
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrgPhoneNumber $orgPhoneNumber)
    {
        //
    }
}
