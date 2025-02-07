<?php

namespace App\Http\Controllers;

use App\Models\StoragePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StoragePackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    try {
        // Fetch active packages
        $packages = StoragePackage::where('is_active', true)->get();

        // Return JSON response with status and data
        return response()->json([
            'status' => true,
            'data' => $packages,
        ]);
    } catch (\Exception $e) {
        // Log the exception for debugging
        Log::error('Error fetching packages: ' . $e->getMessage());

        // Return JSON response with error status
        return response()->json([
            'status' => false,
            'message' => 'An error occurred while fetching packages.',
        ], 500);
    }
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
    public function show(StoragePackage $storagePackage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StoragePackage $storagePackage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StoragePackage $storagePackage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StoragePackage $storagePackage)
    {
        //
    }
}
