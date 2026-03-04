<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\City;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CityManagementTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private string $token;
    private Country $country;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
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
            'active' => true,
        ]);
    }

    public function test_admin_cannot_access_without_jwt(): void
    {
        $response = $this->getJson('/api/admin/cities');
        $response->assertStatus(401);
    }

    public function test_admin_can_create_city(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/cities', [
                'country_uuid' => $this->country->uuid,
                'name' => [
                    'ar' => 'القاهرة',
                    'en' => 'Cairo',
                ],
                'active' => true,
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'uuid',
                'name',
                'active',
            ],
        ]);

        $this->assertDatabaseHas('cities', [
            'country_id' => $this->country->id,
            'name->ar' => 'القاهرة',
            'name->en' => 'Cairo',
        ]);
    }

    public function test_city_requires_valid_country_uuid(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/cities', [
                'country_uuid' => 'invalid-uuid',
                'name' => [
                    'ar' => 'القاهرة',
                    'en' => 'Cairo',
                ],
            ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_update_city(): void
    {
        $city = City::create([
            'country_id' => $this->country->id,
            'name' => ['ar' => 'القاهرة', 'en' => 'Cairo'],
            'active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/admin/cities/{$city->uuid}", [
                'name' => [
                    'ar' => 'القاهرة الكبرى',
                    'en' => 'Greater Cairo',
                ],
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cities', [
            'id' => $city->id,
            'name->en' => 'Greater Cairo',
        ]);
    }

    public function test_admin_can_soft_delete_city(): void
    {
        $city = City::create([
            'country_id' => $this->country->id,
            'name' => ['ar' => 'القاهرة', 'en' => 'Cairo'],
            'active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/admin/cities/{$city->uuid}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('cities', ['id' => $city->id]);
    }

    public function test_admin_can_restore_city(): void
    {
        $city = City::create([
            'country_id' => $this->country->id,
            'name' => ['ar' => 'القاهرة', 'en' => 'Cairo'],
            'active' => true,
        ]);
        $city->delete();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/admin/cities/{$city->uuid}/restore");

        $response->assertStatus(200);
        $this->assertDatabaseHas('cities', [
            'id' => $city->id,
            'deleted_at' => null,
        ]);
    }

    public function test_admin_can_toggle_active(): void
    {
        $city = City::create([
            'country_id' => $this->country->id,
            'name' => ['ar' => 'القاهرة', 'en' => 'Cairo'],
            'active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson("/api/admin/cities/{$city->uuid}/active", [
                'active' => false,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cities', [
            'id' => $city->id,
            'active' => false,
        ]);
    }

    public function test_inactive_admin_blocked(): void
    {
        $this->admin->update(['active' => false]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/admin/cities');

        $response->assertStatus(403);
    }
}
