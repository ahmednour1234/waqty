<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

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

    public function test_upload_rejects_svg(): void
    {
        $svg = UploadedFile::fake()->create('test.svg', 100, 'image/svg+xml');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/categories', [
                'name' => ['ar' => 'فئة', 'en' => 'Category'],
                'image' => $svg,
            ]);

        $response->assertStatus(422);
    }

    public function test_upload_rejects_non_image_files(): void
    {
        $file = UploadedFile::fake()->create('test.php', 100, 'application/x-php');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/categories', [
                'name' => ['ar' => 'فئة', 'en' => 'Category'],
                'image' => $file,
            ]);

        $response->assertStatus(422);
    }

    public function test_upload_validates_file_size(): void
    {
        $largeImage = UploadedFile::fake()->image('test.jpg', 100, 100)->size(3000); // 3MB

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/categories', [
                'name' => ['ar' => 'فئة', 'en' => 'Category'],
                'image' => $largeImage,
            ]);

        $response->assertStatus(422);
    }

    public function test_upload_stores_with_random_name(): void
    {
        $image = UploadedFile::fake()->image('original-name.jpg', 100, 100);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/categories', [
                'name' => ['ar' => 'فئة', 'en' => 'Category'],
                'image' => $image,
            ]);

        $response->assertStatus(201);
        $category = Category::first();
        $this->assertNotNull($category->image_path);
        $this->assertStringNotContainsString('original-name', $category->image_path);
        $this->assertStringEndsWith('.webp', $category->image_path);
    }

    public function test_upload_re_encodes_to_webp(): void
    {
        $image = UploadedFile::fake()->image('test.jpg', 100, 100);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/categories', [
                'name' => ['ar' => 'فئة', 'en' => 'Category'],
                'image' => $image,
            ]);

        $response->assertStatus(201);
        $category = Category::first();
        $this->assertStringEndsWith('.webp', $category->image_path);
    }
}
