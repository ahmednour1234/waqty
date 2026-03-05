<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => ['ar' => 'مطاعم', 'en' => 'Restaurants'],
                'slug' => 'restaurants',
                'active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => ['ar' => 'كافيهات', 'en' => 'Cafes'],
                'slug' => 'cafes',
                'active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => ['ar' => 'تسوق', 'en' => 'Shopping'],
                'slug' => 'shopping',
                'active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => ['ar' => 'ترفيه', 'en' => 'Entertainment'],
                'slug' => 'entertainment',
                'active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => ['ar' => 'خدمات', 'en' => 'Services'],
                'slug' => 'services',
                'active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = Category::where('slug', $categoryData['slug'])->first();
            
            if ($category) {
                $category->update($categoryData);
            } else {
                Category::create($categoryData);
            }
        }

        if ($this->command) {
            $this->command->info('Seeded ' . count($categories) . ' categories.');
        }
    }
}
