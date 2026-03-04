<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CountryManagementTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private string $token;

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
    }

    public function test_admin_cannot_access_without_jwt(): void
    {
        $response = $this->getJson('/api/admin/countries');
        $response->assertStatus(401);
    }

    public function test_admin_can_create_country(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/countries', [
                'name' => [
                    'ar' => 'مصر',
                    'en' => 'Egypt',
                ],
                'iso2' => 'EG',
                'phone_code' => '+20',
                'active' => true,
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'uuid',
                'name',
                'iso2',
                'phone_code',
                'active',
            ],
        ]);

        $this->assertDatabaseHas('countries', [
            'name->ar' => 'مصر',
            'name->en' => 'Egypt',
            'iso2' => 'EG',
        ]);
    }

    public function test_admin_can_update_country(): void
    {
        $country = Country::create([
            'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
            'iso2' => 'EG',
            'active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/admin/countries/{$country->uuid}", [
                'name' => [
                    'ar' => 'جمهورية مصر العربية',
                    'en' => 'Arab Republic of Egypt',
                ],
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('countries', [
            'id' => $country->id,
            'name->en' => 'Arab Republic of Egypt',
        ]);
    }

    public function test_admin_can_soft_delete_country(): void
    {
        $country = Country::create([
            'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
            'active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/admin/countries/{$country->uuid}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('countries', ['id' => $country->id]);
    }

    public function test_admin_can_restore_country(): void
    {
        $country = Country::create([
            'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
            'active' => true,
        ]);
        $country->delete();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/admin/countries/{$country->uuid}/restore");

        $response->assertStatus(200);
        $this->assertDatabaseHas('countries', [
            'id' => $country->id,
            'deleted_at' => null,
        ]);
    }

    public function test_admin_can_toggle_active(): void
    {
        $country = Country::create([
            'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
            'active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson("/api/admin/countries/{$country->uuid}/active", [
                'active' => false,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('countries', [
            'id' => $country->id,
            'active' => false,
        ]);
    }

    public function test_inactive_admin_blocked(): void
    {
        $this->admin->update(['active' => false]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/admin/countries');

        $response->assertStatus(403);
    }
}

