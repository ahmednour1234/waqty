<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Country;
use App\Models\City;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProviderTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private string $token;
    private Country $country;
    private City $city;
    private Provider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
            'active' => true,
        ]);

        $response = $this->postJson('/api/admin/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $this->token = $response->json('data.token');

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
            'password' => Hash::make('password123'),
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'active' => true,
            'blocked' => false,
            'banned' => false,
        ]);
    }

    public function test_admin_routes_require_admin_auth(): void
    {
        $response = $this->getJson('/api/admin/providers');

        $response->assertStatus(401);
    }

    public function test_admin_can_list_providers(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/admin/providers');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    public function test_admin_can_view_provider(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/admin/providers/' . $this->provider->uuid);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Test Provider', $data['name']);
    }

    public function test_admin_can_toggle_active_status(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/admin/providers/' . $this->provider->uuid . '/active', [
                'active' => false,
            ]);

        $response->assertStatus(200);
        $this->provider->refresh();
        $this->assertFalse($this->provider->active);
    }

    public function test_admin_can_block_provider(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/admin/providers/' . $this->provider->uuid . '/block', [
                'blocked' => true,
            ]);

        $response->assertStatus(200);
        $this->provider->refresh();
        $this->assertTrue($this->provider->blocked);
    }

    public function test_admin_can_ban_provider(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/admin/providers/' . $this->provider->uuid . '/ban', [
                'banned' => true,
            ]);

        $response->assertStatus(200);
        $this->provider->refresh();
        $this->assertTrue($this->provider->banned);
        $this->assertTrue($this->provider->blocked);
        $this->assertFalse($this->provider->active);
    }

    public function test_admin_can_soft_delete_provider(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/admin/providers/' . $this->provider->uuid);

        $response->assertStatus(200);
        $this->assertSoftDeleted('providers', ['id' => $this->provider->id]);
    }

    public function test_admin_can_restore_provider(): void
    {
        $this->provider->delete();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/providers/' . $this->provider->uuid . '/restore');

        $response->assertStatus(200);
        $this->provider->refresh();
        $this->assertNull($this->provider->deleted_at);
    }
}

