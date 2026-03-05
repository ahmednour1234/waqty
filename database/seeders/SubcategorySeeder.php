<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class SubcategorySeeder extends Seeder
{
    public function run(): void
    {
        $restaurantCategory = Category::where('slug', 'restaurants')->first();
        $cafeCategory = Category::where('slug', 'cafes')->first();
        $shoppingCategory = Category::where('slug', 'shopping')->first();

        if (!$restaurantCategory || !$cafeCategory || !$shoppingCategory) {
            if ($this->command) {
                $this->command->warn('Categories not found. Please run CategorySeeder first.');
            }
            return;
        }

        $subcategories = [
            [
                'category_id' => $restaurantCategory->id,
                'name' => ['ar' => 'مطاعم عربية', 'en' => 'Arabic Restaurants'],
                'slug' => 'arabic-restaurants',
                'active' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => $restaurantCategory->id,
                'name' => ['ar' => 'مطاعم إيطالية', 'en' => 'Italian Restaurants'],
                'slug' => 'italian-restaurants',
                'active' => true,
                'sort_order' => 2,
            ],
            [
                'category_id' => $restaurantCategory->id,
                'name' => ['ar' => 'مطاعم آسيوية', 'en' => 'Asian Restaurants'],
                'slug' => 'asian-restaurants',
                'active' => true,
                'sort_order' => 3,
            ],
            [
                'category_id' => $cafeCategory->id,
                'name' => ['ar' => 'كافيهات تقليدية', 'en' => 'Traditional Cafes'],
                'slug' => 'traditional-cafes',
                'active' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => $cafeCategory->id,
                'name' => ['ar' => 'كافيهات حديثة', 'en' => 'Modern Cafes'],
                'slug' => 'modern-cafes',
                'active' => true,
                'sort_order' => 2,
            ],
            [
                'category_id' => $shoppingCategory->id,
                'name' => ['ar' => 'ملابس', 'en' => 'Clothing'],
                'slug' => 'clothing',
                'active' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => $shoppingCategory->id,
                'name' => ['ar' => 'إلكترونيات', 'en' => 'Electronics'],
                'slug' => 'electronics',
                'active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($subcategories as $subcategoryData) {
            $subcategory = Subcategory::where('slug', $subcategoryData['slug'])->first();
            
            if ($subcategory) {
                $subcategory->update($subcategoryData);
            } else {
                Subcategory::create($subcategoryData);
            }
        }

        if ($this->command) {
            $this->command->info('Seeded ' . count($subcategories) . ' subcategories.');
        }
    }
}
