<?php

namespace Tests\Feature\Public;

use App\Models\City;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CityListingTest extends TestCase
{
    use RefreshDatabase;

    private Country $country;

    protected function setUp(): void
    {
        parent::setUp();

        $this->country = Country::create([
            'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
            'iso2' => 'EG',
            'active' => true,
        ]);
    }

    public function test_public_can_list_cities_without_auth(): void
    {
        City::create([
            'country_id' => $this->country->id,
            'name' => ['ar' => 'القاهرة', 'en' => 'Cairo'],
            'active' => true,
        ]);

        $response = $this->getJson('/api/public/cities');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'uuid',
                    'name',
                ],
            ],
        ]);
    }

    public function test_public_only_sees_active_cities(): void
    {
        City::create([
            'country_id' => $this->country->id,
            'name' => ['ar' => 'القاهرة', 'en' => 'Cairo'],
            'active' => true,
        ]);

        City::create([
            'country_id' => $this->country->id,
            'name' => ['ar' => 'الإسكندرية', 'en' => 'Alexandria'],
            'active' => false,
        ]);

        $response = $this->getJson('/api/public/cities');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    public function test_public_does_not_see_deleted_cities(): void
    {
        $city = City::create([
            'country_id' => $this->country->id,
            'name' => ['ar' => 'القاهرة', 'en' => 'Cairo'],
            'active' => true,
        ]);

        $city->delete();

        $response = $this->getJson('/api/public/cities');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(0, $data);
    }

    public function test_public_can_filter_by_country_uuid(): void
    {
        $country2 = Country::create([
            'name' => ['ar' => 'السعودية', 'en' => 'Saudi Arabia'],
            'iso2' => 'SA',
            'active' => true,
        ]);

        City::create([
            'country_id' => $this->country->id,
            'name' => ['ar' => 'القاهرة', 'en' => 'Cairo'],
            'active' => true,
        ]);

        City::create([
            'country_id' => $country2->id,
            'name' => ['ar' => 'الرياض', 'en' => 'Riyadh'],
            'active' => true,
        ]);

        $response = $this->getJson("/api/public/cities?country_uuid={$this->country->uuid}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Cairo', $data[0]['name']);
    }

    public function test_localization_works(): void
    {
        City::create([
            'country_id' => $this->country->id,
            'name' => ['ar' => 'القاهرة', 'en' => 'Cairo'],
            'active' => true,
        ]);

        $response = $this->withHeader('Accept-Language', 'ar')
            ->getJson('/api/public/cities');

        $response->assertStatus(200);
        $this->assertEquals('القاهرة', $response->json('data.0.name'));

        $response = $this->withHeader('Accept-Language', 'en')
            ->getJson('/api/public/cities');

        $response->assertStatus(200);
        $this->assertEquals('Cairo', $response->json('data.0.name'));
    }

    public function test_public_returns_limited_fields(): void
    {
        City::create([
            'country_id' => $this->country->id,
            'name' => ['ar' => 'القاهرة', 'en' => 'Cairo'],
            'active' => true,
        ]);

        $response = $this->getJson('/api/public/cities');

        $response->assertStatus(200);
        $data = $response->json('data.0');
        
        $this->assertArrayHasKey('uuid', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayNotHasKey('id', $data);
        $this->assertArrayNotHasKey('country_id', $data);
        $this->assertArrayNotHasKey('active', $data);
        $this->assertArrayNotHasKey('created_at', $data);
        $this->assertArrayNotHasKey('deleted_at', $data);
    }
}
