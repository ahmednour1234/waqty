<?php

namespace Tests\Feature\Public;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_list_categories_without_auth(): void
    {
        Category::create([
            'name' => ['ar' => 'فئة', 'en' => 'Category'],
            'active' => true,
        ]);

        $response = $this->getJson('/api/public/categories');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'uuid',
                    'name',
                ],
            ],
        ]);
    }

    public function test_public_only_sees_active_categories(): void
    {
        Category::create([
            'name' => ['ar' => 'فئة نشطة', 'en' => 'Active Category'],
            'active' => true,
        ]);

        Category::create([
            'name' => ['ar' => 'فئة غير نشطة', 'en' => 'Inactive Category'],
            'active' => false,
        ]);

        $response = $this->getJson('/api/public/categories');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Active Category', $data[0]['name']);
    }

    public function test_public_does_not_see_deleted_categories(): void
    {
        $category = Category::create([
            'name' => ['ar' => 'فئة', 'en' => 'Category'],
            'active' => true,
        ]);

        $category->delete();

        $response = $this->getJson('/api/public/categories');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(0, $data);
    }

    public function test_localization_works(): void
    {
        Category::create([
            'name' => ['ar' => 'فئة', 'en' => 'Category'],
            'active' => true,
        ]);

        $response = $this->withHeader('Accept-Language', 'ar')
            ->getJson('/api/public/categories');

        $response->assertStatus(200);
        $this->assertEquals('فئة', $response->json('data.0.name'));

        $response = $this->withHeader('Accept-Language', 'en')
            ->getJson('/api/public/categories');

        $response->assertStatus(200);
        $this->assertEquals('Category', $response->json('data.0.name'));
    }

    public function test_public_returns_limited_fields(): void
    {
        Category::create([
            'name' => ['ar' => 'فئة', 'en' => 'Category'],
            'active' => true,
        ]);

        $response = $this->getJson('/api/public/categories');

        $response->assertStatus(200);
        $data = $response->json('data.0');
        
        $this->assertArrayHasKey('uuid', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayNotHasKey('id', $data);
        $this->assertArrayNotHasKey('created_at', $data);
        $this->assertArrayNotHasKey('deleted_at', $data);
    }
}
