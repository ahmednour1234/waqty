<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
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
            'password' => Hash::make('password'),
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
        $response = $this->getJson('/api/admin/categories');
        $response->assertStatus(401);
    }

    public function test_admin_can_create_category(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/categories', [
                'name' => [
                    'ar' => 'فئة',
                    'en' => 'Category',
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

        $this->assertDatabaseHas('categories', [
            'name->ar' => 'فئة',
            'name->en' => 'Category',
        ]);
    }

    public function test_admin_can_upload_image(): void
    {
        $image = UploadedFile::fake()->image('test.jpg', 100, 100);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/categories', [
                'name' => [
                    'ar' => 'فئة',
                    'en' => 'Category',
                ],
                'image' => $image,
            ]);

        $response->assertStatus(201);
        $category = Category::first();
        $this->assertNotNull($category->image_path);
    }

    public function test_upload_rejects_svg(): void
    {
        $svg = UploadedFile::fake()->create('test.svg', 100);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/categories', [
                'name' => [
                    'ar' => 'فئة',
                    'en' => 'Category',
                ],
                'image' => $svg,
            ]);

        $response->assertStatus(422);
    }

    public function test_inactive_admin_blocked(): void
    {
        $this->admin->update(['active' => false]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/admin/categories');

        $response->assertStatus(403);
    }
}
