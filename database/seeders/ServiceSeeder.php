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
        $traditionalCafes = Subcategory::where('slug', 'traditional-cafes')->first();
        $modernCafes = Subcategory::where('slug', 'modern-cafes')->first();

        if (!$arabicRestaurants || !$italianRestaurants || !$traditionalCafes || !$modernCafes) {
            if ($this->command) {
                $this->command->warn('Subcategories not found. Please run SubcategorySeeder first.');
            }
            return;
        }

        $services = [
            ['sub_category_id' => $arabicRestaurants->id, 'name' => ['ar' => 'مشاوي مشكلة', 'en' => 'Mixed Grill'], 'description' => ['ar' => 'مشاوي مشكلة متنوعة', 'en' => 'Assorted grilled meats']],
            ['sub_category_id' => $arabicRestaurants->id, 'name' => ['ar' => 'كبسة دجاج', 'en' => 'Chicken Kabsa'], 'description' => ['ar' => 'أرز بسمتي مع دجاج مشوي', 'en' => 'Basmati rice with roasted chicken']],
            ['sub_category_id' => $arabicRestaurants->id, 'name' => ['ar' => 'سلطة فتوش', 'en' => 'Fattoush Salad'], 'description' => ['ar' => 'سلطة خضراء طازجة', 'en' => 'Fresh green salad']],
            ['sub_category_id' => $arabicRestaurants->id, 'name' => ['ar' => 'حمص بالطحينة', 'en' => 'Hummus Tahini'], 'description' => ['ar' => 'حمص كريمي مع طحينة', 'en' => 'Creamy hummus with tahini']],
            ['sub_category_id' => $arabicRestaurants->id, 'name' => ['ar' => 'ورق عنب', 'en' => 'Stuffed Grape Leaves'], 'description' => ['ar' => 'ورق عنب محشو بالأرز والأعشاب', 'en' => 'Grape leaves stuffed with rice and herbs']],
            ['sub_category_id' => $arabicRestaurants->id, 'name' => ['ar' => 'شوربة عدس', 'en' => 'Lentil Soup'], 'description' => ['ar' => 'شوربة عدس دافئة ومتبلة', 'en' => 'Warm seasoned lentil soup']],
            ['sub_category_id' => $arabicRestaurants->id, 'name' => ['ar' => 'كفتة مشوية', 'en' => 'Grilled Kofta'], 'description' => ['ar' => 'كفتة لحم مشوية على الفحم', 'en' => 'Charcoal grilled minced meat kofta']],
            ['sub_category_id' => $arabicRestaurants->id, 'name' => ['ar' => 'أرز بالخلطة', 'en' => 'Oriental Rice'], 'description' => ['ar' => 'أرز بالبهارات والمكسرات', 'en' => 'Spiced rice with nuts']],
            ['sub_category_id' => $arabicRestaurants->id, 'name' => ['ar' => 'شاورما دجاج', 'en' => 'Chicken Shawarma'], 'description' => ['ar' => 'شرائح دجاج متبلة ومشوية', 'en' => 'Seasoned roasted chicken slices']],
            ['sub_category_id' => $arabicRestaurants->id, 'name' => ['ar' => 'كنافة', 'en' => 'Kunafa'], 'description' => ['ar' => 'حلوى كنافة مقرمشة', 'en' => 'Crispy kunafa dessert']],
            ['sub_category_id' => $italianRestaurants->id, 'name' => ['ar' => 'بيتزا مارغريتا', 'en' => 'Margherita Pizza'], 'description' => ['ar' => 'بيتزا بصوص الطماطم والجبن', 'en' => 'Pizza with tomato sauce and cheese']],
            ['sub_category_id' => $italianRestaurants->id, 'name' => ['ar' => 'باستا كاربونارا', 'en' => 'Pasta Carbonara'], 'description' => ['ar' => 'باستا مع صوص الكريمة والبيكون', 'en' => 'Pasta with cream sauce and bacon']],
            ['sub_category_id' => $italianRestaurants->id, 'name' => ['ar' => 'بيتزا بيبروني', 'en' => 'Pepperoni Pizza'], 'description' => ['ar' => 'بيتزا بجبن ومكونات بيبروني', 'en' => 'Pizza topped with pepperoni and cheese']],
            ['sub_category_id' => $italianRestaurants->id, 'name' => ['ar' => 'ريزوتو بالفطر', 'en' => 'Mushroom Risotto'], 'description' => ['ar' => 'ريزوتو كريمي بالفطر', 'en' => 'Creamy mushroom risotto']],
            ['sub_category_id' => $italianRestaurants->id, 'name' => ['ar' => 'لازانيا اللحم', 'en' => 'Beef Lasagna'], 'description' => ['ar' => 'طبقات لازانيا محشوة باللحم', 'en' => 'Layered lasagna with beef filling']],
            ['sub_category_id' => $italianRestaurants->id, 'name' => ['ar' => 'باستا ألفريدو', 'en' => 'Chicken Alfredo'], 'description' => ['ar' => 'باستا بصوص ألفريدو والدجاج', 'en' => 'Pasta with Alfredo sauce and chicken']],
            ['sub_category_id' => $italianRestaurants->id, 'name' => ['ar' => 'بروشيتا', 'en' => 'Bruschetta'], 'description' => ['ar' => 'خبز محمص بالطماطم والريحان', 'en' => 'Toasted bread with tomato and basil']],
            ['sub_category_id' => $italianRestaurants->id, 'name' => ['ar' => 'نيوكي بالجبن', 'en' => 'Cheese Gnocchi'], 'description' => ['ar' => 'نيوكي طري بصوص الجبن', 'en' => 'Soft gnocchi with cheese sauce']],
            ['sub_category_id' => $italianRestaurants->id, 'name' => ['ar' => 'تيراميسو', 'en' => 'Tiramisu'], 'description' => ['ar' => 'حلوى إيطالية بالقهوة', 'en' => 'Italian coffee-flavored dessert']],
            ['sub_category_id' => $italianRestaurants->id, 'name' => ['ar' => 'شوربة مينيستروني', 'en' => 'Minestrone Soup'], 'description' => ['ar' => 'شوربة خضار إيطالية', 'en' => 'Italian vegetable soup']],
            ['sub_category_id' => $traditionalCafes->id, 'name' => ['ar' => 'قهوة عربية', 'en' => 'Arabic Coffee'], 'description' => ['ar' => 'قهوة عربية أصيلة بالهيل', 'en' => 'Traditional Arabic coffee with cardamom']],
            ['sub_category_id' => $traditionalCafes->id, 'name' => ['ar' => 'شاي بالنعناع', 'en' => 'Mint Tea'], 'description' => ['ar' => 'شاي ساخن بالنعناع الطازج', 'en' => 'Hot tea with fresh mint']],
            ['sub_category_id' => $traditionalCafes->id, 'name' => ['ar' => 'ينسون', 'en' => 'Anise Tea'], 'description' => ['ar' => 'مشروب ينسون دافئ', 'en' => 'Warm anise herbal tea']],
            ['sub_category_id' => $traditionalCafes->id, 'name' => ['ar' => 'كركديه', 'en' => 'Hibiscus Tea'], 'description' => ['ar' => 'مشروب كركديه بارد أو ساخن', 'en' => 'Hot or cold hibiscus drink']],
            ['sub_category_id' => $traditionalCafes->id, 'name' => ['ar' => 'سحلب', 'en' => 'Sahlab'], 'description' => ['ar' => 'مشروب سحلب كريمي بالقرفة', 'en' => 'Creamy sahlab with cinnamon']],
            ['sub_category_id' => $traditionalCafes->id, 'name' => ['ar' => 'شاي أحمر', 'en' => 'Black Tea'], 'description' => ['ar' => 'شاي أحمر كلاسيكي', 'en' => 'Classic black tea']],
            ['sub_category_id' => $traditionalCafes->id, 'name' => ['ar' => 'تمر وقهوة', 'en' => 'Dates and Coffee'], 'description' => ['ar' => 'تقديم تمر مع قهوة عربية', 'en' => 'Dates served with Arabic coffee']],
            ['sub_category_id' => $traditionalCafes->id, 'name' => ['ar' => 'زنجبيل بالعسل', 'en' => 'Ginger Honey Tea'], 'description' => ['ar' => 'مشروب زنجبيل بالعسل', 'en' => 'Ginger tea sweetened with honey']],
            ['sub_category_id' => $traditionalCafes->id, 'name' => ['ar' => 'قرفة بالحليب', 'en' => 'Cinnamon Milk'], 'description' => ['ar' => 'حليب ساخن بالقرفة', 'en' => 'Warm milk with cinnamon']],
            ['sub_category_id' => $traditionalCafes->id, 'name' => ['ar' => 'شاي أخضر', 'en' => 'Green Tea'], 'description' => ['ar' => 'شاي أخضر منعش', 'en' => 'Refreshing green tea']],
            ['sub_category_id' => $modernCafes->id, 'name' => ['ar' => 'لاتيه', 'en' => 'Latte'], 'description' => ['ar' => 'قهوة لاتيه مع الحليب المبخر', 'en' => 'Latte coffee with steamed milk']],
            ['sub_category_id' => $modernCafes->id, 'name' => ['ar' => 'كابتشينو', 'en' => 'Cappuccino'], 'description' => ['ar' => 'كابتشينو إيطالي أصيل', 'en' => 'Authentic Italian cappuccino']],
            ['sub_category_id' => $modernCafes->id, 'name' => ['ar' => 'موهيتو', 'en' => 'Mojito'], 'description' => ['ar' => 'موهيتو منعش بدون كحول', 'en' => 'Refreshing non-alcoholic mojito']],
            ['sub_category_id' => $modernCafes->id, 'name' => ['ar' => 'فلات وايت', 'en' => 'Flat White'], 'description' => ['ar' => 'قهوة فلات وايت بحليب ناعم', 'en' => 'Flat white with silky milk']],
            ['sub_category_id' => $modernCafes->id, 'name' => ['ar' => 'إسبريسو مزدوج', 'en' => 'Double Espresso'], 'description' => ['ar' => 'جرعة إسبريسو مركزة', 'en' => 'Strong double shot espresso']],
            ['sub_category_id' => $modernCafes->id, 'name' => ['ar' => 'آيس لاتيه', 'en' => 'Iced Latte'], 'description' => ['ar' => 'لاتيه بارد ومنعش', 'en' => 'Refreshing iced latte']],
            ['sub_category_id' => $modernCafes->id, 'name' => ['ar' => 'فرابتشينو', 'en' => 'Frappuccino'], 'description' => ['ar' => 'مشروب قهوة مثلج مخفوق', 'en' => 'Blended iced coffee drink']],
            ['sub_category_id' => $modernCafes->id, 'name' => ['ar' => 'كرواسون زبدة', 'en' => 'Butter Croissant'], 'description' => ['ar' => 'كرواسون طازج بالزبدة', 'en' => 'Fresh buttery croissant']],
            ['sub_category_id' => $modernCafes->id, 'name' => ['ar' => 'تشيز كيك', 'en' => 'Cheesecake'], 'description' => ['ar' => 'قطعة تشيز كيك كريمية', 'en' => 'Slice of creamy cheesecake']],
            ['sub_category_id' => $modernCafes->id, 'name' => ['ar' => 'براوني شوكولاتة', 'en' => 'Chocolate Brownie'], 'description' => ['ar' => 'براوني شوكولاتة غنية', 'en' => 'Rich chocolate brownie']],
        ];

        $count = 0;
        foreach ($services as $serviceData) {
            $existing = Service::where('name->en', $serviceData['name']['en'])->first();
            if (!$existing) {
                DB::table('services')->insert([
                    'uuid'            => (string) Str::ulid(),
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
