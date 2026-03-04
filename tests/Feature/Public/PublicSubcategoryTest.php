<?php

namespace Tests\Feature\Public;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSubcategoryTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;
    private Subcategory $subcategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => ['ar' => 'فئة', 'en' => 'Category'],
            'active' => true,
        ]);

        $this->subcategory = Subcategory::create([
            'category_id' => $this->category->id,
            'name' => ['ar' => 'فئة فرعية', 'en' => 'Subcategory'],
            'active' => true,
        ]);
    }

    public function test_public_can_list_subcategories(): void
    {
        $response = $this->getJson('/api/public/subcategories');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    public function test_public_can_filter_subcategories_by_category(): void
    {
        $response = $this->getJson('/api/public/subcategories?category_uuid=' . $this->category->uuid);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    public function test_public_only_sees_active_subcategories(): void
    {
        Subcategory::create([
            'category_id' => $this->category->id,
            'name' => ['ar' => 'غير نشط', 'en' => 'Inactive'],
            'active' => false,
        ]);

        $response = $this->getJson('/api/public/subcategories');

        $response->assertStatus(200);
        $data = $response->json('data');
        $inactiveFound = collect($data)->contains(function ($item) {
            return $item['name'] === 'Inactive';
        });
        $this->assertFalse($inactiveFound);
    }
}
