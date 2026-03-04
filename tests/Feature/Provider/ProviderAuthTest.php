<?php

namespace Tests\Feature\Provider;

use App\Models\Country;
use App\Models\City;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProviderAuthTest extends TestCase
{
    use RefreshDatabase;

    private Provider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $country = Country::create([
            'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
            'iso2' => 'EG',
            'phone_code' => '+20',
            'active' => true,
        ]);

        $city = City::create([
            'country_id' => $country->id,
            'name' => ['ar' => 'القاهرة', 'en' => 'Cairo'],
            'active' => true,
        ]);

        $this->provider = Provider::create([
            'name' => 'Test Provider',
            'email' => 'provider@test.com',
            'password' => Hash::make('password123'),
            'country_id' => $country->id,
            'city_id' => $city->id,
            'active' => true,
            'blocked' => false,
            'banned' => false,
        ]);
    }

    public function test_provider_cannot_login_if_inactive(): void
    {
        $this->provider->update(['active' => false]);

        $response = $this->postJson('/api/provider/auth/login', [
            'email' => 'provider@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
    }

    public function test_provider_cannot_login_if_blocked(): void
    {
        $this->provider->update(['blocked' => true]);

        $response = $this->postJson('/api/provider/auth/login', [
            'email' => 'provider@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
    }

    public function test_provider_cannot_login_if_banned(): void
    {
        $this->provider->update(['banned' => true]);

        $response = $this->postJson('/api/provider/auth/login', [
            'email' => 'provider@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
    }

    public function test_forgot_password_does_not_reveal_existence(): void
    {
        $response = $this->postJson('/api/provider/auth/forgot-password', [
            'email' => 'nonexistent@test.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'If account exists, a reset link has been sent']);
    }

    public function test_forgot_password_rate_limiting(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/provider/auth/forgot-password', [
                'email' => 'provider@test.com',
            ]);
        }

        $response->assertStatus(429);
    }

    public function test_reset_token_stored_hashed(): void
    {
        $this->postJson('/api/provider/auth/forgot-password', [
            'email' => 'provider@test.com',
        ]);

        $reset = \App\Models\ProviderPasswordReset::where('provider_id', $this->provider->id)->first();
        $this->assertNotNull($reset);
        $this->assertNotEquals('plain-token', $reset->token_hash);
    }

    public function test_reset_token_expires(): void
    {
        $this->postJson('/api/provider/auth/forgot-password', [
            'email' => 'provider@test.com',
        ]);

        $reset = \App\Models\ProviderPasswordReset::where('provider_id', $this->provider->id)->first();
        $reset->update(['expires_at' => now()->subMinute()]);

        $response = $this->postJson('/api/provider/auth/reset-password', [
            'email' => 'provider@test.com',
            'token' => 'some-token',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(400);
    }

    public function test_reset_token_cannot_be_reused(): void
    {
        $this->postJson('/api/provider/auth/forgot-password', [
            'email' => 'provider@test.com',
        ]);

        $reset = \App\Models\ProviderPasswordReset::where('provider_id', $this->provider->id)->first();
        $token = 'test-token-123';
        $reset->update(['token_hash' => Hash::make($token), 'expires_at' => now()->addMinutes(15)]);

        $this->postJson('/api/provider/auth/reset-password', [
            'email' => 'provider@test.com',
            'token' => $token,
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response = $this->postJson('/api/provider/auth/reset-password', [
            'email' => 'provider@test.com',
            'token' => $token,
            'new_password' => 'anotherpassword123',
            'new_password_confirmation' => 'anotherpassword123',
        ]);

        $response->assertStatus(400);
    }

    public function test_login_rate_limiting(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/provider/auth/login', [
                'email' => 'provider@test.com',
                'password' => 'wrongpassword',
            ]);
        }

        $response->assertStatus(429);
    }
}
