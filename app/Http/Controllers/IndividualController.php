<?php

namespace App\Http\Controllers;

use App\Models\Individual;
use Illuminate\Http\Request;

class IndividualController extends Controller
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
        $individualData = Individual::where('user_id', $id)->first();
    
        if ($individualData) {
            return response()->json(['status' => true, 'data' => $individualData]);
        } else {
            return response()->json(['status' => false, 'message' => 'Individual data not found']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Individual $individual)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Individual $individual)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Individual $individual)
    {
        //
    }
}
