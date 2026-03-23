<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\Service;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $provider = Provider::where('email', 'provider@example.com')->first();

        if (!$provider) {
            if ($this->command) {
                $this->command->warn('Provider not found. Please run ProviderSeeder first.');
            }
            return;
        }

        $arabicRestaurants = Subcategory::where('slug', 'arabic-restaurants')->first();
        $italianRestaurants = Subcategory::where('slug', 'italian-restaurants')->first();
        $traditionalCafes  = Subcategory::where('slug', 'traditional-cafes')->first();
        $modernCafes       = Subcategory::where('slug', 'modern-cafes')->first();

        if (!$arabicRestaurants) {
            if ($this->command) {
                $this->command->warn('Subcategories not found. Please run SubcategorySeeder first.');
            }
            return;
        }

        $services = [
            // Arabic Restaurants
            [
                'sub_category_id' => $arabicRestaurants->id,
                'name'            => ['ar' => 'مشاوي مشكلة', 'en' => 'Mixed Grill'],
                'description'     => ['ar' => 'مشاوي مشكلة متنوعة', 'en' => 'Assorted grilled meats'],
            ],
            [
                'sub_category_id' => $arabicRestaurants->id,
                'name'            => ['ar' => 'كبسة دجاج', 'en' => 'Chicken Kabsa'],
                'description'     => ['ar' => 'أرز بسمتي مع دجاج مشوي', 'en' => 'Basmati rice with roasted chicken'],
            ],
            [
                'sub_category_id' => $arabicRestaurants->id,
                'name'            => ['ar' => 'سلطة فتوش', 'en' => 'Fattoush Salad'],
                'description'     => ['ar' => 'سلطة خضراء طازجة', 'en' => 'Fresh green salad'],
            ],
            // Italian Restaurants
            [
                'sub_category_id' => $italianRestaurants ? $italianRestaurants->id : null,
                'name'            => ['ar' => 'بيتزا مارغريتا', 'en' => 'Margherita Pizza'],
                'description'     => ['ar' => 'بيتزا بصوص الطماطم والجبن', 'en' => 'Pizza with tomato sauce and cheese'],
            ],
            [
                'sub_category_id' => $italianRestaurants ? $italianRestaurants->id : null,
                'name'            => ['ar' => 'باستا كاربونارا', 'en' => 'Pasta Carbonara'],
                'description'     => ['ar' => 'باستا مع صوص الكريمة والبيكون', 'en' => 'Pasta with cream sauce and bacon'],
            ],
            // Traditional Cafes
            [
                'sub_category_id' => $traditionalCafes ? $traditionalCafes->id : null,
                'name'            => ['ar' => 'قهوة عربية', 'en' => 'Arabic Coffee'],
                'description'     => ['ar' => 'قهوة عربية أصيلة بالهيل', 'en' => 'Traditional Arabic coffee with cardamom'],
            ],
            [
                'sub_category_id' => $traditionalCafes ? $traditionalCafes->id : null,
                'name'            => ['ar' => 'شاي بالنعناع', 'en' => 'Mint Tea'],
                'description'     => ['ar' => 'شاي ساخن بالنعناع الطازج', 'en' => 'Hot tea with fresh mint'],
            ],
            // Modern Cafes
            [
                'sub_category_id' => $modernCafes ? $modernCafes->id : null,
                'name'            => ['ar' => 'لاتيه', 'en' => 'Latte'],
                'description'     => ['ar' => 'قهوة لاتيه مع الحليب المبخر', 'en' => 'Latte coffee with steamed milk'],
            ],
            [
                'sub_category_id' => $modernCafes ? $modernCafes->id : null,
                'name'            => ['ar' => 'كابتشينو', 'en' => 'Cappuccino'],
                'description'     => ['ar' => 'كابتشينو إيطالي أصيل', 'en' => 'Authentic Italian cappuccino'],
            ],
            [
                'sub_category_id' => $modernCafes ? $modernCafes->id : null,
                'name'            => ['ar' => 'موهيتو', 'en' => 'Mojito'],
                'description'     => ['ar' => 'موهيتو منعش بدون كحول', 'en' => 'Refreshing non-alcoholic mojito'],
            ],
        ];

        $count = 0;
        foreach ($services as $serviceData) {
            $existing = Service::where('name->en', $serviceData['name']['en'])->first();
            if (!$existing) {
                DB::table('services')->insert([
                    'uuid'            => (string) Str::ulid(),
                    'provider_id'     => $provider->id,
                    'sub_category_id' => $serviceData['sub_category_id'],
                    'name'            => json_encode($serviceData['name']),
                    'description'     => isset($serviceData['description']) ? json_encode($serviceData['description']) : null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                $count++;
            }
        }

        if ($this->command) {
            $this->command->info("Seeded {$count} services.");
        }
    }
}
