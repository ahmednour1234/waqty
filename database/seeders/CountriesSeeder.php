<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('iso2', 'EG')->first();
        
        if ($country) {
            $country->update([
                'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
                'phone_code' => '+20',
                'active' => true,
                'sort_order' => 1,
            ]);
        } else {
            Country::create([
                'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
                'iso2' => 'EG',
                'phone_code' => '+20',
                'active' => true,
                'sort_order' => 1,
            ]);
        }
    }
}
