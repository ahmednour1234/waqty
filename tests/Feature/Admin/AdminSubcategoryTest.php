<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSubcategoryTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private string $token;
    private Category $category;

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

        $this->category = Category::create([
            'name' => ['ar' => 'فئة', 'en' => 'Category'],
            'active' => true,
        ]);
    }

    public function test_admin_can_list_subcategories(): void
    {
        Subcategory::create([
            'category_id' => $this->category->id,
            'name' => ['ar' => 'فئة فرعية', 'en' => 'Subcategory'],
            'active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/admin/subcategories');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    public function test_admin_can_create_subcategory(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/subcategories', [
                'category_uuid' => $this->category->uuid,
                'name' => [
                    'ar' => 'فئة فرعية',
                    'en' => 'Subcategory',
                ],
                'active' => true,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('subcategories', [
            'category_id' => $this->category->id,
        ]);
    }

    public function test_admin_can_view_subcategory(): void
    {
        $subcategory = Subcategory::create([
            'category_id' => $this->category->id,
            'name' => ['ar' => 'فئة فرعية', 'en' => 'Subcategory'],
            'active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/admin/subcategories/' . $subcategory->uuid);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($subcategory->uuid, $data['uuid']);
    }

    public function test_admin_can_update_subcategory(): void
    {
        $subcategory = Subcategory::create([
            'category_id' => $this->category->id,
            'name' => ['ar' => 'فئة فرعية', 'en' => 'Subcategory'],
            'active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/admin/subcategories/' . $subcategory->uuid, [
                'name' => [
                    'ar' => 'محدث',
                    'en' => 'Updated',
                ],
            ]);

        $response->assertStatus(200);
        $subcategory->refresh();
        $this->assertEquals('Updated', $subcategory->name['en']);
    }

    public function test_admin_can_delete_subcategory(): void
    {
        $subcategory = Subcategory::create([
            'category_id' => $this->category->id,
            'name' => ['ar' => 'فئة فرعية', 'en' => 'Subcategory'],
            'active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/admin/subcategories/' . $subcategory->uuid);

        $response->assertStatus(200);
        $this->assertSoftDeleted('subcategories', ['id' => $subcategory->id]);
    }

    public function test_admin_can_toggle_active_status(): void
    {
        $subcategory = Subcategory::create([
            'category_id' => $this->category->id,
            'name' => ['ar' => 'فئة فرعية', 'en' => 'Subcategory'],
            'active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/admin/subcategories/' . $subcategory->uuid . '/active', [
                'active' => false,
            ]);

        $response->assertStatus(200);
        $subcategory->refresh();
        $this->assertFalse($subcategory->active);
    }

    public function test_admin_can_restore_subcategory(): void
    {
        $subcategory = Subcategory::create([
            'category_id' => $this->category->id,
            'name' => ['ar' => 'فئة فرعية', 'en' => 'Subcategory'],
            'active' => true,
        ]);
        $subcategory->delete();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/subcategories/' . $subcategory->uuid . '/restore');

        $response->assertStatus(200);
        $subcategory->refresh();
        $this->assertNull($subcategory->deleted_at);
    }

    public function test_admin_routes_require_authentication(): void
    {
        $response = $this->getJson('/api/admin/subcategories');
        $response->assertStatus(401);
    }
}
