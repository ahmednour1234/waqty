<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    public function run(): void
    {
        Country::updateOrCreate(
            ['iso2' => 'EG'],
            [
                'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
                'iso2' => 'EG',
                'phone_code' => '+20',
                'active' => true,
                'sort_order' => 1,
            ]
        );
    }
}
