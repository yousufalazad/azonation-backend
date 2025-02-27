<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    try {
        // Fetch active packages
        $packages = Package::where('status', true)->get();

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
    public function show(Package $package)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $package)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package)
    {
        //
    }
}
