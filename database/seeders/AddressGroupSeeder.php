<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
// php artisan db:seed --class=AddressGroupSeeder

class AddressGroupSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $groups = [
            [
                'name'  => 'Global',
                'alias' => 'GLOBAL',
            ],
            [
                'name'  => 'Africa',
                'alias' => 'AFRICA',
            ],
            [
                'name'  => 'Europe',
                'alias' => 'EUROPE',
            ],
            [
                'name'  => 'Middle East',
                'alias' => 'MIDDLE_EAST',
            ],
            [
                'name'  => 'South Asia',
                'alias' => 'SOUTH_ASIA',
            ],
            [
                'name'  => 'East Asia',
                'alias' => 'EAST_ASIA',
            ],
            [
                'name'  => 'Southeast Asia',
                'alias' => 'SOUTHEAST_ASIA',
            ],
            [
                'name'  => 'Central Asia',
                'alias' => 'CENTRAL_ASIA',
            ],
            [
                'name'  => 'North America',
                'alias' => 'NORTH_AMERICA',
            ],
            [
                'name'  => 'South America',
                'alias' => 'SOUTH_AMERICA',
            ],
            [
                'name'  => 'Oceania',
                'alias' => 'OCEANIA',
            ],
        ];

        foreach ($groups as $group) {
            DB::table('address_groups')->updateOrInsert(
                ['alias' => $group['alias']], // unique key
                [
                    'name'       => $group['name'],
                    'is_active'  => 1,
                    'created_at'=> $now,
                    'updated_at'=> $now,
                ]
            );
        }
    }
}
