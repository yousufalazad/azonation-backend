<?php

namespace App\Http\Controllers;

use App\Models\UserPriceRate;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Subscription;
use App\Models\UserCountry;
use App\Models\UserCurrency;
use App\Models\PriceRate;
use Illuminate\Support\Facades\DB;


class UserPriceRateController extends Controller
{

public function getUserPriceRates()
{
    // Fetch users with relationships
    $users = User::with(['subscription.package', 'country', 'currency'])->get();

    // Fetch all price rates
    $priceRates = PriceRate::all();

    // Map data to include price rate
    $userPriceRates = $users->map(function ($user) use ($priceRates) {
        // Fetch the region column dynamically based on the user's country
        $regionKey = $this->getRegionKeyByCountry($user->country->id);

        // Find the relevant price rate for the user's package
        $priceRate = $priceRates->firstWhere('package_id', optional($user->subscription->package)->id);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'package' => optional($user->subscription->package)->name ?? 'Unknown',
            'country' => $user->country->name ?? 'Unknown',
            'currency' => $user->currency->symbol ?? '',
            'price_rate' => $priceRate ? $priceRate->{$regionKey} : 'N/A',
        ];
    });

    return response()->json([
        'status' => true,
        'data' => $userPriceRates
    ]);
}

/**
 * Get the region key by country ID.
 *
 * @param int $countryId
 * @return string
 */
private function getRegionKeyByCountry($countryId)
{
    // Map country IDs to their respective regions
    $countryRegionMap = [
        // Map your country IDs to corresponding region keys
        1 => 'region1', // Example: Rest of the World
        2 => 'region2', // UK
        3 => 'region3', // USA
        // Add more mappings based on your region data
    ];

    return $countryRegionMap[$countryId] ?? 'region1'; // Default to 'region1' if not found
}




    // public function index()
    // {
    //     // Fetch all necessary data with relationships
    //     $users = User::with(['subscription.package', 'country', 'currency'])->get();
    //     $priceRates = PriceRate::all();

    //     // Map data to include price rate
    //     $userPriceRates = $users->map(function ($user) use ($priceRates) {
    //         $priceRate = $priceRates->firstWhere(function ($rate) use ($user) {

    //             return $rate->package_id == $user->subscription->package->id &&
    //                 $rate->country_id == $user->country->id;
    //         });

    //         return [
    //             'id' => $user->id,
    //             'name' => $user->name,
    //             'package' => $user->subscription->package->name ?? 'Unknown',
    //             'country' => $user->country->name ?? 'Unknown',
    //             'currency' => $user->currency->symbol ?? '',
    //             'price_rate' => $priceRate->rate ?? 'N/A',
    //         ];
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'data' => $userPriceRates
    //     ]);
    // }

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
    public function show(UserPriceRate $userPriceRate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserPriceRate $userPriceRate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserPriceRate $userPriceRate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserPriceRate $userPriceRate)
    {
        //
    }
}
