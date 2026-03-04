<?php

namespace Tests\Feature\Seeders;

use App\Models\City;
use App\Models\Country;
use Database\Seeders\CitiesSeeder;
use Database\Seeders\CountriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountriesCitiesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_countries_seeder_inserts_egypt(): void
    {
        $seeder = new CountriesSeeder();
        $seeder->run();

        $this->assertDatabaseHas('countries', [
            'iso2' => 'EG',
            'name->ar' => 'مصر',
            'name->en' => 'Egypt',
            'phone_code' => '+20',
            'active' => true,
        ]);
    }

    public function test_cities_seeder_inserts_cities(): void
    {
        $countrySeeder = new CountriesSeeder();
        $countrySeeder->run();

        $citySeeder = new CitiesSeeder();
        $citySeeder->run();

        $egypt = Country::where('iso2', 'EG')->first();
        $citiesCount = City::where('country_id', $egypt->id)->count();

        $this->assertGreaterThan(0, $citiesCount);
        $this->assertDatabaseHas('cities', [
            'country_id' => $egypt->id,
            'name->en' => 'Cairo',
        ]);
    }

    public function test_seeder_does_not_create_duplicates(): void
    {
        $countrySeeder = new CountriesSeeder();
        $countrySeeder->run();
        $countrySeeder->run();

        $countriesCount = Country::where('iso2', 'EG')->count();
        $this->assertEquals(1, $countriesCount);

        $citySeeder = new CitiesSeeder();
        $citySeeder->run();
        $citySeeder->run();

        $egypt = Country::where('iso2', 'EG')->first();
        $cairoCount = City::where('country_id', $egypt->id)
            ->whereRaw("JSON_EXTRACT(name, '$.en') = ?", ['Cairo'])
            ->count();

        $this->assertEquals(1, $cairoCount);
    }
}
