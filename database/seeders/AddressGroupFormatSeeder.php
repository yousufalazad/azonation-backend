<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// php artisan db:seed --class=AddressGroupFormatSeeder

class AddressGroupFormatSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        /**
         * GLOBAL fallback format
         * Used for all regions unless overridden
         */
        $globalFormat = [
            'labels' => [
                'line1'    => 'Address line 1',
                'line2'    => 'Address line 2 (optional)',
                'city'     => 'City',
                'region'   => 'State/Region (optional)',
                'postcode' => 'Postcode (optional)',
            ],
            'fields'   => ['line1', 'line2', 'city', 'region', 'postcode'],
            'required' => ['line1', 'city'],
            'format'   => [
                '{{line1}}',
                '{{line2}}',
                '{{city}}',
                '{{region}}',
                '{{postcode}}',
            ],
            'uppercase' => [],
        ];

        /**
         * Custom formats per group
         * Only define where different from GLOBAL
         */
        $customFormats = [

            'EUROPE' => [
                'labels' => [
                    'line1'    => 'Address line 1',
                    'line2'    => 'Address line 2 (optional)',
                    'city'     => 'Town/City',
                    'region'   => 'County/Region (optional)',
                    'postcode' => 'Postcode',
                ],
                'fields'   => ['line1', 'line2', 'city', 'region', 'postcode'],
                'required' => ['line1', 'city', 'postcode'],
                'format'   => [
                    '{{line1}}',
                    '{{line2}}',
                    '{{city}}',
                    '{{region}}',
                    '{{postcode}}',
                ],
                'uppercase' => ['postcode'],
            ],

            'NORTH_AMERICA' => [
                'labels' => [
                    'line1'    => 'Street address',
                    'line2'    => 'Apt/Suite (optional)',
                    'city'     => 'City',
                    'region'   => 'State/Province',
                    'postcode' => 'ZIP/Postal code',
                ],
                'fields'   => ['line1', 'line2', 'city', 'region', 'postcode'],
                'required' => ['line1', 'city', 'region', 'postcode'],
                'format'   => [
                    '{{line1}}',
                    '{{line2}}',
                    '{{city}}, {{region}} {{postcode}}',
                ],
                'uppercase' => [],
            ],

            'MIDDLE_EAST' => [
                'labels' => [
                    'line1'    => 'Building/Street',
                    'line2'    => 'District/Area (optional)',
                    'city'     => 'City',
                    'region'   => 'Region/Province (optional)',
                    'postcode' => 'Postal code (optional)',
                ],
                'fields'   => ['line1', 'line2', 'city', 'region', 'postcode'],
                'required' => ['line1', 'city'],
                'format'   => [
                    '{{line1}}',
                    '{{line2}}',
                    '{{city}}',
                    '{{region}}',
                    '{{postcode}}',
                ],
                'uppercase' => [],
            ],

            'SOUTH_ASIA' => [
                'labels' => [
                    'line1'    => 'House/Road/Area',
                    'line2'    => 'Locality/Village (optional)',
                    'city'     => 'City/Town',
                    'region'   => 'District/State',
                    'postcode' => 'Postcode (optional)',
                ],
                'fields'   => ['line1', 'line2', 'city', 'region', 'postcode'],
                'required' => ['line1', 'city', 'region'],
                'format'   => [
                    '{{line1}}',
                    '{{line2}}',
                    '{{city}}',
                    '{{region}}',
                    '{{postcode}}',
                ],
                'uppercase' => [],
            ],
        ];

        /**
         * Fetch ALL address groups
         */
        $groups = DB::table('address_groups')->get();

        foreach ($groups as $group) {

            // Use custom format or fallback to GLOBAL
            $components = $customFormats[$group->alias] ?? $globalFormat;

            DB::table('address_group_formats')->updateOrInsert(
                [
                    'address_group_id'    => $group->id,
                    'address_group_alias' => $group->alias,
                ],
                [
                    'format_components' => json_encode($components, JSON_UNESCAPED_UNICODE),
                    'is_active'         => 1,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]
            );
        }
    }
}
