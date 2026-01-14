<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
// php artisan db:seed --class=AddressCountryGroupSeeder

class AddressCountryGroupSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Fetch all address groups indexed by alias
        $groups = DB::table('address_groups')
            ->select('id', 'alias')
            ->pluck('id', 'alias')
            ->toArray();

        // Fetch all countries indexed by name
        $countries = DB::table('countries')
            ->select('id', 'name')
            ->pluck('id', 'name')
            ->toArray();

        $data = [];

        /**
         * Helper function
         */
        $mapCountries = function ($alias, array $countryNames) use (
            &$data, $groups, $countries, $now
        ) {
            if (!isset($groups[$alias])) {
                return;
            }

            foreach ($countryNames as $name) {
                if (!isset($countries[$name])) {
                    continue;
                }

                $data[] = [
                    'address_group_id'     => $groups[$alias],
                    'country_id'           => $countries[$name],
                    'address_group_alias'  => $alias,
                    'is_active'            => 1,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ];
            }
        };

        /**
         * GLOBAL → ALL COUNTRIES
         */
        foreach ($countries as $countryId) {
            $data[] = [
                'address_group_id'     => $groups['GLOBAL'],
                'country_id'           => $countryId,
                'address_group_alias'  => 'GLOBAL',
                'is_active'            => 1,
                'created_at'           => $now,
                'updated_at'           => $now,
            ];
        }

        /**
         * AFRICA (54)
         */
        $mapCountries('AFRICA', [
            'Algeria','Angola','Benin','Botswana','Burkina Faso','Burundi',
            'Cabo Verde','Cameroon','Central African Republic','Chad','Comoros',
            'Congo','Costa d\'Ivoire','Democratic Republic of the Congo','Djibouti',
            'Egypt','Equatorial Guinea','Eritrea','Eswatini','Ethiopia','Gabon',
            'Gambia','Ghana','Guinea','Guinea-Bissau','Kenya','Lesotho','Liberia',
            'Libya','Madagascar','Malawi','Mali','Mauritania','Mauritius','Morocco',
            'Mozambique','Namibia','Niger','Nigeria','Rwanda',
            'Sao Tome and Principe','Senegal','Seychelles','Sierra Leone','Somalia',
            'South Africa','South Sudan','Sudan','Tanzania','Togo','Tunisia',
            'Uganda','Zambia','Zimbabwe'
        ]);

        /**
         * SOUTH ASIA (8)
         */
        $mapCountries('SOUTH_ASIA', [
            'Afghanistan','Bangladesh','Bhutan','India',
            'Maldives','Nepal','Pakistan','Sri Lanka'
        ]);

        /**
         * MIDDLE EAST (14)
         */
        $mapCountries('MIDDLE_EAST', [
            'Bahrain','Iran','Iraq','Israel','Jordan','Kuwait','Lebanon',
            'Oman','Qatar','Saudi Arabia','Syria',
            'United Arab Emirates','Yemen','Palestine'
        ]);

        /**
         * EAST ASIA (5)
         */
        $mapCountries('EAST_ASIA', [
            'China','Japan','Mongolia','North Korea','South Korea'
        ]);

        /**
         * SOUTHEAST ASIA (11)
         */
        $mapCountries('SOUTHEAST_ASIA', [
            'Brunei','Cambodia','Indonesia','Laos','Malaysia',
            'Myanmar','Philippines','Singapore','Thailand',
            'Vietnam','Timor-Leste'
        ]);

        /**
         * CENTRAL ASIA (5)
         */
        $mapCountries('CENTRAL_ASIA', [
            'Kazakhstan','Kyrgyzstan','Tajikistan',
            'Turkmenistan','Uzbekistan'
        ]);

        /**
         * EUROPE (44)
         */
        $mapCountries('EUROPE', [
            'Albania','Andorra','Austria','Belarus','Belgium',
            'Bosnia and Herzegovina','Bulgaria','Croatia','Cyprus',
            'Czech Republic','Denmark','Estonia','Finland','France',
            'Germany','Greece','Hungary','Iceland','Ireland','Italy',
            'Latvia','Liechtenstein','Lithuania','Luxembourg','Malta',
            'Moldova','Monaco','Montenegro','Netherlands',
            'North Macedonia','Norway','Poland','Portugal','Romania',
            'Russia','San Marino','Serbia','Slovakia','Slovenia',
            'Spain','Sweden','Switzerland','Ukraine',
            'United Kingdom','Vatican City'
        ]);

        /**
         * NORTH AMERICA (23)
         */
        $mapCountries('NORTH_AMERICA', [
            'Antigua and Barbuda','Bahamas','Barbados','Belize',
            'Canada','Costa Rica','Cuba','Dominica','Dominican Republic',
            'El Salvador','Grenada','Guatemala','Haiti','Honduras',
            'Jamaica','Mexico','Nicaragua','Panama',
            'Saint Kitts and Nevis','Saint Lucia',
            'Saint Vincent and the Grenadines',
            'Trinidad and Tobago','United States'
        ]);

        /**
         * SOUTH AMERICA (12)
         */
        $mapCountries('SOUTH_AMERICA', [
            'Argentina','Bolivia','Brazil','Chile','Colombia',
            'Ecuador','Guyana','Paraguay','Peru',
            'Suriname','Uruguay','Venezuela'
        ]);

        /**
         * OCEANIA (14)
         */
        $mapCountries('OCEANIA', [
            'Australia','Fiji','Kiribati','Marshall Islands',
            'Micronesia','Nauru','New Zealand','Palau',
            'Papua New Guinea','Samoa','Solomon Islands',
            'Tonga','Tuvalu','Vanuatu'
        ]);

        /**
         * Insert safely (ignore duplicates)
         */
        DB::table('address_country_groups')->insertOrIgnore($data);
    }
}