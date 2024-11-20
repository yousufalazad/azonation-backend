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
use Illuminate\Support\Facades\Log;



class UserPriceRateController extends Controller
{

    public function getUserPriceRates()
    {
        try {
            // Fetch users with relationships
            // $users = User::with(['subscription.package', 'country', 'currency'])->get();
            $users = User::with(['subscription.package', 'country', 'currency'])->get();


            // Fetch all price rates
            $priceRates = PriceRate::all();

            // Map data to include price rate
            $userPriceRates = $users->map(function ($user) use ($priceRates) {
                // Check if the user has a subscription and package
                $packageName = optional($user->subscription)->package->name ?? 'Unknown';

                // Fetch the region column dynamically based on the user's country
                $regionKey = $this->getRegionKeyByCountry($user->country->id);

                // Find the relevant price rate for the user's package
                $priceRate = $priceRates->firstWhere('package_id', optional($user->subscription->package)->id);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'package' => $packageName,
                    'country' => $user->country->name ?? 'Unknown',
                    'currency' => $user->currency->symbol ?? '',
                    'price_rate' => $priceRate ? $priceRate->{$regionKey} : 'N/A',
                ];
            });

            return response()->json([
                'status' => true,
                'data' => $userPriceRates
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error fetching user price rates: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return error details in the response
            return response()->json([
                'status' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            ], 500); // 500 Internal Server Error
        }
    }



    private function getRegionKeyByCountry($countryId)
    {
        switch ($countryId) {

                // countries.id for United Kingdom, GBP
            case 184:
                return 'region2';

                // USA, USD
            case 185:
                return 'region3';

                // Canada, CAD
            case 32:
                return 'region4';

                // European Union (EU), 27 countries, EUR
            case 10: //Austria
            case 17: //Belgium
            case 26: //Bulgaria
            case 42: //Croatia//
            case 44: //Cyprus
            case 45: //Czech Republic
            case 46: //Denmark
            case 55: //Estonia
            case 59: //Finland
            case 60: //France
            case 64: //Germany
            case 66: //Greece
            case 74: //Hungary
            case 80: //Ireland
            case 82: //Italy
            case 94: //Latvia
            case 100: //Lithuania
            case 101: //Luxembourg
            case 107: //Malta
            case 123: //Netherlands
            case 138: //Poland
            case 139: //Portugal
            case 141: //Romania
            case 156: //Slovakia
            case 157: //Slovenia
            case 162: //Spain
            case 166: //Sweden
                return 'region5';

                // China, CNY
            case 36:
                return 'region6';

                // Bangladesh, BDT
            case 14:
                return 'region7';

                // India, INR
            case 76:
                return 'region8';

                // Japan, JPY
            case 84:
                return 'region9';

                //Malaysia, MYR
            case 104:
                return 'region10';

                //Russia, RUB
            case 142:
                return 'region11';

                //Australia and New Zealand, AUD
            case 9:
            case 124:
                return 'region12';

                //3 non-EU members of the European Free Trade Association (EFTA), EUR
            case 75: //Iceland
            case 99: //Liechtenstein
            case 129: //Norway
                return 'region13';

                //South American Countries (12 Total), USD
            case 7: //Argentina
            case 21: //Bolivia
            case 24: //Brazil
            case 35: //Chile
            case 37: //Colombia
            case 50: //Ecuador
            case 71: //Guyana
            case 135: //Paraguay
            case 136: //Peru
            case 165: //Suriname
            case 186: //Uruguay
            case 190: //Venezuela
                return 'region14';

                //Middle Eastern, Western Asia, (16 Total), USD
            case 1: //Afghanistan
            case 13: //Bahrain
                //case 44: // Cyprus, included in EU
            case 51: // Egypt
            case 78: // Iran
            case 79: // Iraq
            case 81: // Israel
            case 85: // Jordan
            case 91: // Kuwait
            case 95: // Lebanon
            case 130: // Oman
            case 195: // Palestine
            case 140: // Qatar
            case 150: // Saudi Arabia
            case 168: // Syria
            case 174: // Turkey
            case 183: // United Arab Emirates (UAE)
            case 192: // Yemen
                return 'region15';

                //South Asian countries excluding Bangladesh, China, Malaysia, and India:
            case 20: //Bhutan
            case 122: // Nepal
            case 131: // Pakistan
            case 163: // Sri Lanka
            case 105: // Maldives
                return 'region16';

                //African countries, (Total 54), USD:
            case 160: //South Africa
                return 'region17';

            case 999:
                return 'region18';

            case 888:
                return 'region19';

            case 777:
                return 'region20';


                // Default to Rest of the World if no match
            default:
                return 'region1';
        }
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
