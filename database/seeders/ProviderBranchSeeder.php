<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Provider;
use App\Models\ProviderBranch;
use Illuminate\Database\Seeder;

class ProviderBranchSeeder extends Seeder
{
    public function run(): void
    {
        $provider = Provider::where('email', 'provider@example.com')->first();
        
        if (!$provider) {
            if ($this->command) {
                $this->command->warn('Provider not found. Please run ProviderSeeder first.');
            }
            return;
        }

        $egypt = Country::where('iso2', 'EG')->first();
        
        if (!$egypt) {
            if ($this->command) {
                $this->command->warn('Egypt country not found. Please run CountriesSeeder first.');
            }
            return;
        }

        $city = City::where('country_id', $egypt->id)->where('active', true)->first();
        
        if (!$city) {
            if ($this->command) {
                $this->command->warn('No active cities found. Please run CitiesSeeder first.');
            }
            return;
        }

        $mainBranch = ProviderBranch::where('provider_id', $provider->id)
            ->where('is_main', true)
            ->first();

        if (!$mainBranch) {
            ProviderBranch::create([
                'provider_id' => $provider->id,
                'name' => 'Main Branch',
                'phone' => '+201234567890',
                'country_id' => $egypt->id,
                'city_id' => $city->id,
                'latitude' => 30.0444,
                'longitude' => 31.2357,
                'is_main' => true,
                'active' => true,
                'blocked' => false,
                'banned' => false,
            ]);
        } else {
            $mainBranch->update([
                'name' => 'Main Branch',
                'phone' => '+201234567890',
                'country_id' => $egypt->id,
                'city_id' => $city->id,
                'latitude' => 30.0444,
                'longitude' => 31.2357,
                'active' => true,
                'blocked' => false,
                'banned' => false,
            ]);
        }

        $secondaryBranch = ProviderBranch::where('provider_id', $provider->id)
            ->where('is_main', false)
            ->first();

        if (!$secondaryBranch) {
            ProviderBranch::create([
                'provider_id' => $provider->id,
                'name' => 'Secondary Branch',
                'phone' => '+201234567891',
                'country_id' => $egypt->id,
                'city_id' => $city->id,
                'latitude' => 30.0500,
                'longitude' => 31.2400,
                'is_main' => false,
                'active' => true,
                'blocked' => false,
                'banned' => false,
            ]);
        } else {
            $secondaryBranch->update([
                'name' => 'Secondary Branch',
                'phone' => '+201234567891',
                'country_id' => $egypt->id,
                'city_id' => $city->id,
                'latitude' => 30.0500,
                'longitude' => 31.2400,
                'active' => true,
                'blocked' => false,
                'banned' => false,
            ]);
        }

        if ($this->command) {
            $this->command->info('Seeded provider branches for provider: ' . $provider->email);
        }
    }
}
