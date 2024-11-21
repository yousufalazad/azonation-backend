<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\PriceRate;
use Illuminate\Support\Facades\Log;

class UserPriceRateController extends Controller
{

    public function getUserPriceRates(Request $request)
{
    try {
        // Retrieve the authenticated user's ID
        $user_id = $request->user()->id;

        // Fetch package_id, country_id, and currency_code for the user
        $packageIdAndCountryData = Subscription::where('subscriptions.user_id', $user_id)
            ->leftJoin('user_countries', 'subscriptions.user_id', '=', 'user_countries.user_id')
            ->leftJoin('user_currencies', 'subscriptions.user_id', '=', 'user_currencies.user_id')
            ->leftJoin('currencies', 'user_currencies.currency_id', '=', 'currencies.id')
            ->select('subscriptions.package_id', 'user_countries.country_id', 'currencies.currency_code')
            ->first(); // Retrieve a single record

        if (!$packageIdAndCountryData) {
            return response()->json([
                'status' => false,
                'message' => 'No package or country data found for the user.'
            ], 404);
        }

        // Extract package_id and country_id
        $packageId = $packageIdAndCountryData->package_id;
        $countryId = $packageIdAndCountryData->country_id;
        $currencyCode = $packageIdAndCountryData->currency_code;

        // Determine the regionKey based on the country_id
        $regionKey = $this->getRegionKeyByCountry($countryId);

        // Validate regionKey
        if (!$regionKey) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to determine region key for the given country.'
            ], 400);
        }

        // Fetch the price rate for the specified package and region
        $userPriceRate = PriceRate::where('package_id', $packageId)
            ->select($regionKey) // Fetch only the relevant region column
            ->first();

        if (!$userPriceRate) {
            return response()->json([
                'status' => false,
                'message' => 'No price rate found for the provided package and region.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => 1,
                'package_id' => $packageId,
                'region' => $regionKey,
                'price' => $userPriceRate->$regionKey,
                'currency_code' => $currencyCode
            ]
        ]);
    } catch (\Exception $e) {
        // Log the error for debugging purposes
        Log::error('Error fetching user price rates: ' . $e->getMessage(), [
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        // Return error details in the response
        return response()->json([
            'status' => false,
            'error' => [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]
        ], 500);
    }
}



    private function getRegionKeyByCountry($UserCountryId)
    {
        switch ($UserCountryId) {

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
}
