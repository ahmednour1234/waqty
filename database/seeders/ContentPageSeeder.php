<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\ContentPage;
use Illuminate\Database\Seeder;

class ContentPageSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::first();

        $pages = [
            [
                'slug'       => 'terms-conditions',
                'title_en'   => 'Terms & Conditions',
                'title_ar'   => 'الشروط والأحكام',
                'content_en' => 'Full terms and conditions content goes here...',
                'content_ar' => 'محتوى الشروط والأحكام الكامل يأتي هنا...',
                'active'     => true,
            ],
            [
                'slug'       => 'privacy-policy',
                'title_en'   => 'Privacy Policy',
                'title_ar'   => 'سياسة الخصوصية',
                'content_en' => 'Full privacy policy content goes here...',
                'content_ar' => 'محتوى سياسة الخصوصية الكامل يأتي هنا...',
                'active'     => true,
            ],
            [
                'slug'       => 'faq',
                'title_en'   => 'FAQ',
                'title_ar'   => 'الأسئلة الشائعة',
                'content_en' => 'Frequently asked questions content goes here...',
                'content_ar' => 'محتوى الأسئلة الشائعة يأتي هنا...',
                'active'     => true,
            ],
            [
                'slug'       => 'about',
                'title_en'   => 'About Hagzy',
                'title_ar'   => 'عن هاقزي',
                'content_en' => 'About Hagzy content goes here...',
                'content_ar' => 'محتوى عن هاقزي يأتي هنا...',
                'active'     => true,
            ],
        ];

        foreach ($pages as $page) {
            ContentPage::firstOrCreate(
                ['slug' => $page['slug']],
                array_merge($page, ['updated_by_admin_id' => $admin?->id])
            );
        }
    }
}
