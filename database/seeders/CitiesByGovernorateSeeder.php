<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Governorate;
use Illuminate\Database\Seeder;

class CitiesByGovernorateSeeder extends Seeder
{
    public function run(): void
    {
        $egypt = Country::where('iso2', 'EG')->first();

        if (!$egypt) {
            if ($this->command) {
                $this->command->warn('Egypt country not found. Please run CountriesSeeder first.');
            }
            return;
        }

        $cities = require database_path('seeders/data/egypt_cities_by_governorate.php');

        $governorateCache = [];
        $seeded = 0;

        foreach ($cities as $index => $cityData) {
            $governorateName = $cityData['governorate'];

            if (!isset($governorateCache[$governorateName])) {
                $governorate = Governorate::withTrashed()
                    ->whereRaw("JSON_EXTRACT(name, '$.en') = ?", [$governorateName])
                    ->first();

                $governorateCache[$governorateName] = $governorate;
            }

            $governorate = $governorateCache[$governorateName];

            $existing = City::withTrashed()
                ->where('country_id', $egypt->id)
                ->whereRaw("JSON_EXTRACT(name, '$.en') = ?", [$cityData['name']['en']])
                ->first();

            $payload = [
                'name'           => $cityData['name'],
                'country_id'     => $egypt->id,
                'governorate_id' => $governorate?->id,
                'active'         => true,
                'sort_order'     => $index + 1,
            ];

            if ($existing) {
                $existing->restore();
                $existing->update($payload);
            } else {
                City::create($payload);
            }

            $seeded++;
        }

        if ($this->command) {
            $this->command->info("Seeded {$seeded} cities by governorate for Egypt.");
        }
    }
}
