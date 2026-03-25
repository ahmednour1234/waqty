<?php

namespace Database\Seeders;

use App\Models\Governorate;
use Illuminate\Database\Seeder;

class GovernoratesSeeder extends Seeder
{
    public function run(): void
    {
        $governorates = [
            ['ar' => 'القاهرة',          'en' => 'Cairo'],
            ['ar' => 'الجيزة',           'en' => 'Giza'],
            ['ar' => 'الإسكندرية',       'en' => 'Alexandria'],
            ['ar' => 'الدقهلية',         'en' => 'Dakahlia'],
            ['ar' => 'البحر الأحمر',     'en' => 'Red Sea'],
            ['ar' => 'البحيرة',          'en' => 'El Beheira'],
            ['ar' => 'الفيوم',           'en' => 'El Fayoum'],
            ['ar' => 'الغربية',          'en' => 'El Gharbiya'],
            ['ar' => 'الإسماعيلية',      'en' => 'El Ismailia'],
            ['ar' => 'المنوفية',         'en' => 'El Menoufia'],
            ['ar' => 'المنيا',           'en' => 'El Minya'],
            ['ar' => 'القليوبية',        'en' => 'El Qalyubia'],
            ['ar' => 'الوادي الجديد',    'en' => 'El Wadi El Gedid'],
            ['ar' => 'السويس',           'en' => 'El Suez'],
            ['ar' => 'أسوان',            'en' => 'Aswan'],
            ['ar' => 'أسيوط',            'en' => 'Asyut'],
            ['ar' => 'بني سويف',         'en' => 'Beni Suef'],
            ['ar' => 'بورسعيد',          'en' => 'Port Said'],
            ['ar' => 'دمياط',            'en' => 'Damietta'],
            ['ar' => 'جنوب سيناء',       'en' => 'South Sinai'],
            ['ar' => 'كفر الشيخ',        'en' => 'Kafr El Sheikh'],
            ['ar' => 'مطروح',            'en' => 'Matrouh'],
            ['ar' => 'الأقصر',           'en' => 'Luxor'],
            ['ar' => 'الشرقية',          'en' => 'El Sharkia'],
            ['ar' => 'شمال سيناء',       'en' => 'North Sinai'],
            ['ar' => 'سوهاج',            'en' => 'Sohag'],
            ['ar' => 'قنا',              'en' => 'Qena'],
            ['ar' => 'قنا',              'en' => 'Helwan'],
        ];

        // Override last to correct Helwan
        $governorates[27] = ['ar' => 'حلوان / السادس من أكتوبر', 'en' => '6th of October / Helwan'];

        foreach ($governorates as $index => $names) {
            $existing = Governorate::withTrashed()
                ->whereRaw("JSON_EXTRACT(name, '$.en') = ?", [$names['en']])
                ->first();

            if ($existing) {
                $existing->update([
                    'name' => ['ar' => $names['ar'], 'en' => $names['en']],
                    'active' => true,
                    'sort_order' => $index + 1,
                ]);
            } else {
                Governorate::create([
                    'name' => ['ar' => $names['ar'], 'en' => $names['en']],
                    'active' => true,
                    'sort_order' => $index + 1,
                ]);
            }
        }

        if ($this->command) {
            $this->command->info('Seeded ' . count($governorates) . ' governorates for Egypt.');
        }
    }
}
