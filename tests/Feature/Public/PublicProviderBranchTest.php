<?php

namespace Tests\Feature\Public;

use App\Models\City;
use App\Models\Country;
use App\Models\Provider;
use App\Models\ProviderBranch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PublicProviderBranchTest extends TestCase
{
    use RefreshDatabase;

    private Provider $provider;
    private Country $egypt;
    private City $city;

    protected function setUp(): void
    {
        parent::setUp();

        $this->egypt = Country::create([
            'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
            'iso2' => 'EG',
            'active' => true,
        ]);

        $this->city = City::create([
            'country_id' => $this->egypt->id,
            'name' => ['ar' => 'القاهرة', 'en' => 'Cairo'],
            'active' => true,
        ]);

        $this->provider = Provider::create([
            'name' => 'Test Provider',
            'email' => 'provider@test.com',
            'password' => 'password',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
            'active' => true,
            'blocked' => false,
            'banned' => false,
        ]);
    }

    public function test_public_can_list_active_branches(): void
    {
        ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name' => 'Active Branch',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
            'active' => true,
            'blocked' => false,
            'banned' => false,
        ]);

        ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name' => 'Inactive Branch',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
            'active' => false,
            'blocked' => false,
            'banned' => false,
        ]);

        $response = $this->getJson('/api/public/provider-branches');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Active Branch', $data[0]['name']);
    }

    public function test_public_does_not_see_blocked_branches(): void
    {
        ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name' => 'Blocked Branch',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
            'active' => true,
            'blocked' => true,
            'banned' => false,
        ]);

        $response = $this->getJson('/api/public/provider-branches');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(0, $data);
    }

    public function test_public_returns_minimal_fields(): void
    {
        ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name' => 'Test Branch',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
            'active' => true,
            'blocked' => false,
            'banned' => false,
        ]);

        $response = $this->getJson('/api/public/provider-branches');

        $response->assertStatus(200);
        $data = $response->json('data.0');

        $this->assertArrayHasKey('uuid', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('city_name', $data);
        $this->assertArrayNotHasKey('phone', $data);
        $this->assertArrayNotHasKey('active', $data);
        $this->assertArrayNotHasKey('blocked', $data);
        $this->assertArrayNotHasKey('created_at', $data);
    }

    public function test_public_can_filter_by_provider_uuid(): void
    {
        $otherProvider = Provider::create([
            'name' => 'Other Provider',
            'email' => 'other@test.com',
            'password' => 'password',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
            'active' => true,
            'blocked' => false,
            'banned' => false,
        ]);

        ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name' => 'Branch 1',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
            'active' => true,
            'blocked' => false,
            'banned' => false,
        ]);

        ProviderBranch::create([
            'provider_id' => $otherProvider->id,
            'name' => 'Branch 2',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
            'active' => true,
            'blocked' => false,
            'banned' => false,
        ]);

        $response = $this->getJson('/api/public/provider-branches?provider_uuid=' . $this->provider->uuid);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Branch 1', $data[0]['name']);
    }

    public function test_localization_works(): void
    {
        ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name' => 'Test Branch',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
            'active' => true,
            'blocked' => false,
            'banned' => false,
        ]);

        $response = $this->withHeader('Accept-Language', 'ar')
            ->getJson('/api/public/provider-branches');

        $response->assertStatus(200);
        $this->assertEquals('القاهرة', $response->json('data.0.city_name'));

        $response = $this->withHeader('Accept-Language', 'en')
            ->getJson('/api/public/provider-branches');

        $response->assertStatus(200);
        $this->assertEquals('Cairo', $response->json('data.0.city_name'));
    }
}

