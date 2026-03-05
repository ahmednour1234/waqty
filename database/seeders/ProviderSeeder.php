<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Provider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProviderSeeder extends Seeder
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

        $city = City::where('country_id', $egypt->id)->where('active', true)->first();
        
        if (!$city) {
            if ($this->command) {
                $this->command->warn('No active cities found. Please run CitiesSeeder first.');
            }
            return;
        }

        $category = Category::where('active', true)->first();
        
        if (!$category) {
            $category = Category::create([
                'name' => ['ar' => 'فئة عامة', 'en' => 'General Category'],
                'active' => true,
                'sort_order' => 1,
            ]);
        }

        $provider = Provider::where('email', 'provider@example.com')->first();
        
        if ($provider) {
            $provider->update([
                'name' => 'Test Provider',
                'password' => Hash::make('Provider@12345'),
                'phone' => '+201234567890',
                'code' => 'PROV001',
                'category_id' => $category->id,
                'country_id' => $egypt->id,
                'city_id' => $city->id,
                'active' => true,
                'blocked' => false,
                'banned' => false,
            ]);
        } else {
            Provider::create([
                'name' => 'Test Provider',
                'email' => 'provider@example.com',
                'password' => Hash::make('Provider@12345'),
                'phone' => '+201234567890',
                'code' => 'PROV001',
                'category_id' => $category->id,
                'country_id' => $egypt->id,
                'city_id' => $city->id,
                'active' => true,
                'blocked' => false,
                'banned' => false,
            ]);
        }

        if ($this->command) {
            $this->command->info('Seeded provider: provider@example.com (password: Provider@12345)');
        }
    }
}
