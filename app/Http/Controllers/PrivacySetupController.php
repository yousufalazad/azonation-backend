<?php

namespace App\Http\Controllers;

use App\Models\PrivacySetup;
use Illuminate\Http\Request;

class PrivacySetupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $privacySetups = PrivacySetup::where('status', 1)
            ->orderBy('id', 'asc')
            ->get();
        return response()->json([
            'status' => true,
            'data' => $privacySetups
        ]);
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
    public function show(PrivacySetup $privacySetup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PrivacySetup $privacySetup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PrivacySetup $privacySetup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PrivacySetup $privacySetup)
    {
        //
    }
}
