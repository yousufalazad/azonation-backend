<?php

namespace App\Http\Controllers;

use App\Models\AssetLifecycleStatus;
use Illuminate\Http\Request;

class AssetLifecycleStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assetLifecycleStatus = AssetLifecycleStatus::where('is_active', 1)
            ->orderBy('id', 'asc')
            ->get();
        return response()->json([
            'status' => true,
            'data' => $assetLifecycleStatus
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
    public function show(AssetLifecycleStatus $assetLifecycleStatus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AssetLifecycleStatus $assetLifecycleStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AssetLifecycleStatus $assetLifecycleStatus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetLifecycleStatus $assetLifecycleStatus)
    {
        //
    }
}
