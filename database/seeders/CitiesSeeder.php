<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Seeder;

class CitiesSeeder extends Seeder
{
    public function run(): void
    {
        $egypt = Country::where('iso2', 'EG')->first();

        if (!$egypt) {
            $this->command->warn('Egypt country not found. Please run CountriesSeeder first.');
            return;
        }

        $cities = require database_path('seeders/data/egypt_cities.php');

        foreach ($cities as $index => $cityData) {
            $existing = City::where('country_id', $egypt->id)
                ->whereRaw("JSON_EXTRACT(name, '$.en') = ?", [$cityData['name']['en']])
                ->first();

            if ($existing) {
                $existing->update([
                    'name' => $cityData['name'],
                    'active' => true,
                    'sort_order' => $index + 1,
                ]);
            } else {
                City::create([
                    'country_id' => $egypt->id,
                    'name' => $cityData['name'],
                    'active' => true,
                    'sort_order' => $index + 1,
                ]);
            }
        }

        $this->command->info('Seeded ' . count($cities) . ' cities for Egypt.');
    }
}
