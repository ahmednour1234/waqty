<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
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
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/admin/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'token',
                'token_type',
                'expires_in',
                'admin' => [
                    'id',
                    'name',
                    'email',
                    'active',
                ],
            ],
        ]);
    }

    public function test_admin_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/admin/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['success' => false]);
    }

    public function test_admin_cannot_login_if_inactive(): void
    {
        $this->admin->update(['active' => false]);

        $response = $this->postJson('/api/admin/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
    }

    public function test_admin_can_logout(): void
    {
        $loginResponse = $this->postJson('/api/admin/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/admin/auth/logout');

        $response->assertStatus(200);
    }

    public function test_admin_can_get_current_user(): void
    {
        $loginResponse = $this->postJson('/api/admin/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/auth/me');

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'email' => 'admin@test.com',
                'name' => 'Test Admin',
            ],
        ]);
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/admin/auth/logout');

        $response->assertStatus(401);
    }

    public function test_me_requires_authentication(): void
    {
        $response = $this->getJson('/api/admin/auth/me');

        $response->assertStatus(401);
    }
}
