<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
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

        $response->assertStatus(200);
        $this->token = $response->json('data.token') ?? '';
        $this->assertNotEmpty($this->token, 'Failed to get token from login response');
    }

    public function test_admin_can_list_admins(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/admin/admins');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    public function test_admin_can_create_admin(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/admins', [
                'name' => 'New Admin',
                'email' => 'newadmin@test.com',
                'password' => 'password123',
                'active' => true,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('admins', [
            'email' => 'newadmin@test.com',
        ]);
    }

    public function test_admin_can_view_admin(): void
    {
        $newAdmin = Admin::create([
            'name' => 'View Admin',
            'email' => 'view@test.com',
            'password' => 'password',
            'active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/admin/admins/' . $newAdmin->id);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('view@test.com', $data['email']);
    }

    public function test_admin_can_update_admin(): void
    {
        $newAdmin = Admin::create([
            'name' => 'Update Admin',
            'email' => 'update@test.com',
            'password' => 'password',
            'active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/admin/admins/' . $newAdmin->id, [
                'name' => 'Updated Name',
                'email' => 'updated@test.com',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('admins', [
            'id' => $newAdmin->id,
            'name' => 'Updated Name',
            'email' => 'updated@test.com',
        ]);
    }

    public function test_admin_can_toggle_active_status(): void
    {
        $newAdmin = Admin::create([
            'name' => 'Toggle Admin',
            'email' => 'toggle@test.com',
            'password' => 'password',
            'active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/admin/admins/' . $newAdmin->id . '/active', [
                'active' => false,
            ]);

        $response->assertStatus(200);
        $newAdmin->refresh();
        $this->assertFalse($newAdmin->active);
    }

    public function test_admin_routes_require_authentication(): void
    {
        $response = $this->getJson('/api/admin/admins');
        $response->assertStatus(401);
    }
}
