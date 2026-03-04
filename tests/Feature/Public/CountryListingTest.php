<?php

namespace Tests\Feature\Public;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_list_countries_without_auth(): void
    {
        Country::create([
            'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
            'active' => true,
        ]);

        $response = $this->getJson('/api/public/countries');

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

    public function test_public_only_sees_active_countries(): void
    {
        Country::create([
            'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
            'active' => true,
        ]);

        Country::create([
            'name' => ['ar' => 'السعودية', 'en' => 'Saudi Arabia'],
            'active' => false,
        ]);

        $response = $this->getJson('/api/public/countries');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    public function test_public_does_not_see_deleted_countries(): void
    {
        $country = Country::create([
            'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
            'active' => true,
        ]);

        $country->delete();

        $response = $this->getJson('/api/public/countries');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(0, $data);
    }

    public function test_localization_works(): void
    {
        Country::create([
            'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
            'active' => true,
        ]);

        $response = $this->withHeader('Accept-Language', 'ar')
            ->getJson('/api/public/countries');

        $response->assertStatus(200);
        $this->assertEquals('مصر', $response->json('data.0.name'));

        $response = $this->withHeader('Accept-Language', 'en')
            ->getJson('/api/public/countries');

        $response->assertStatus(200);
        $this->assertEquals('Egypt', $response->json('data.0.name'));
    }

    public function test_public_returns_limited_fields(): void
    {
        Country::create([
            'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
            'active' => true,
        ]);

        $response = $this->getJson('/api/public/countries');

        $response->assertStatus(200);
        $data = $response->json('data.0');
        
        $this->assertArrayHasKey('uuid', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayNotHasKey('id', $data);
        $this->assertArrayNotHasKey('active', $data);
        $this->assertArrayNotHasKey('created_at', $data);
        $this->assertArrayNotHasKey('deleted_at', $data);
    }
}
