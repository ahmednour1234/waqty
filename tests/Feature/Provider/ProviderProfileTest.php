<?php

namespace Tests\Feature\Provider;

use App\Models\Country;
use App\Models\City;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProviderProfileTest extends TestCase
{
    use RefreshDatabase;

    private Provider $provider;
    private string $token;
    private Country $country;
    private City $city;

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

        $this->provider = Provider::create([
            'name' => 'Test Provider',
            'email' => 'provider@test.com',
            'password' => 'password123',
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'active' => true,
            'blocked' => false,
            'banned' => false,
        ]);

        $response = $this->postJson('/api/provider/auth/login', [
            'email' => 'provider@test.com',
            'password' => 'password123',
        ]);

        $this->token = $this->getTokenFromLoginResponse($response);
    }

    public function test_provider_can_update_profile(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/provider/profile', [
                'name' => 'Updated Provider',
                'phone' => '+1234567890',
                'city_id' => $this->city->id,
            ]);

        $response->assertStatus(200);
        $this->provider->refresh();
        $this->assertEquals('Updated Provider', $this->provider->name);
    }

    public function test_provider_profile_requires_authentication(): void
    {
        $response = $this->putJson('/api/provider/profile', [
            'name' => 'Updated Provider',
            'city_id' => 1,
        ]);

        $response->assertStatus(401);
    }
}
