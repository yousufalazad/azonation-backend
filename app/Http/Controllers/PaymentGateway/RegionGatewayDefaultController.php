<?php

namespace App\Http\Controllers\PaymentGateway;
use App\Http\Controllers\Controller;

use App\Models\RegionGatewayDefault;
use Illuminate\Http\Request;

class RegionGatewayDefaultController extends Controller
{
    public function stats()
{
    $totalsByEnvironment = RegionGatewayDefault::selectRaw('environment, COUNT(*) as count')
        ->groupBy('environment')
        ->pluck('count', 'environment');

    $totalsByGateway = RegionGatewayDefault::selectRaw('gateway, COUNT(*) as count')
        ->where('status', 'active')
        ->groupBy('gateway')
        ->pluck('count', 'gateway');

    $mostUsedGateway = $totalsByGateway->sortDesc()->keys()->first();

    return response()->json([
        'by_environment' => $totalsByEnvironment,
        'by_gateway' => $totalsByGateway,
        'most_used_gateway' => $mostUsedGateway,
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(RegionGatewayDefault $regionGatewayDefault)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RegionGatewayDefault $regionGatewayDefault)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RegionGatewayDefault $regionGatewayDefault)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RegionGatewayDefault $regionGatewayDefault)
    {
        //
    }
}
