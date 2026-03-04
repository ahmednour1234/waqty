<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_accept_language_header_works(): void
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

    public function test_default_language_fallback(): void
    {
        Category::create([
            'name' => ['ar' => 'فئة', 'en' => 'Category'],
            'active' => true,
        ]);

        $response = $this->getJson('/api/public/categories');

        $response->assertStatus(200);
        $this->assertEquals('فئة', $response->json('data.0.name'));
    }

    public function test_returns_correct_localized_name(): void
    {
        Category::create([
            'name' => ['ar' => 'الطعام', 'en' => 'Food'],
            'active' => true,
        ]);

        $response = $this->withHeader('Accept-Language', 'en')
            ->getJson('/api/public/categories');

        $response->assertStatus(200);
        $this->assertEquals('Food', $response->json('data.0.name'));
    }
}
