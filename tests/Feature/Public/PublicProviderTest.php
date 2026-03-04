<?php

namespace Tests\Feature\Public;

use App\Models\Category;
use App\Models\Country;
use App\Models\City;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProviderTest extends TestCase
{
    use RefreshDatabase;

    private Country $country;
    private City $city;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->country = Country::create([
            'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
            'iso2' => 'EG',
            'phone_code' => '+20',
            'active' => true,
        ]);

        $this->city = City::create([
            'country_id' => $this->country->id,
            'name' => ['ar' => 'القاهرة', 'en' => 'Cairo'],
            'active' => true,
        ]);

        $this->category = Category::create([
            'name' => ['ar' => 'فئة', 'en' => 'Category'],
            'active' => true,
        ]);
    }

    public function test_public_list_returns_only_active_providers(): void
    {
        Provider::create([
            'name' => 'Active Provider',
            'email' => 'active@test.com',
            'password' => 'password',
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'active' => true,
            'blocked' => false,
            'banned' => false,
        ]);

        Provider::create([
            'name' => 'Inactive Provider',
            'email' => 'inactive@test.com',
            'password' => 'password',
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'active' => false,
            'blocked' => false,
            'banned' => false,
        ]);

        $response = $this->getJson('/api/public/providers');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Active Provider', $data[0]['name']);
    }

    public function test_public_list_excludes_blocked_providers(): void
    {
        Provider::create([
            'name' => 'Blocked Provider',
            'email' => 'blocked@test.com',
            'password' => 'password',
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'active' => true,
            'blocked' => true,
            'banned' => false,
        ]);

        $response = $this->getJson('/api/public/providers');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(0, $data);
    }

    public function test_public_list_excludes_banned_providers(): void
    {
        Provider::create([
            'name' => 'Banned Provider',
            'email' => 'banned@test.com',
            'password' => 'password',
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'active' => true,
            'blocked' => false,
            'banned' => true,
        ]);

        $response = $this->getJson('/api/public/providers');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(0, $data);
    }

    public function test_public_list_excludes_deleted_providers(): void
    {
        $provider = Provider::create([
            'name' => 'Deleted Provider',
            'email' => 'deleted@test.com',
            'password' => 'password',
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'active' => true,
            'blocked' => false,
            'banned' => false,
        ]);

        $provider->delete();

        $response = $this->getJson('/api/public/providers');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(0, $data);
    }

    public function test_public_resource_exposes_minimal_fields(): void
    {
        $provider = Provider::create([
            'name' => 'Test Provider',
            'email' => 'test@test.com',
            'password' => 'password',
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'active' => true,
            'blocked' => false,
            'banned' => false,
        ]);

        $response = $this->getJson('/api/public/providers/' . $provider->uuid);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertArrayHasKey('uuid', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayNotHasKey('email', $data);
        $this->assertArrayNotHasKey('phone', $data);
        $this->assertArrayNotHasKey('active', $data);
        $this->assertArrayNotHasKey('blocked', $data);
        $this->assertArrayNotHasKey('banned', $data);
    }

    public function test_public_list_supports_localization(): void
    {
        $provider = Provider::create([
            'name' => 'Test Provider',
            'email' => 'test@test.com',
            'password' => 'password',
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'active' => true,
            'blocked' => false,
            'banned' => false,
        ]);

        $response = $this->withHeader('Accept-Language', 'ar')
            ->getJson('/api/public/providers/' . $provider->uuid);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('فئة', $data['category']['name']);
    }
}
