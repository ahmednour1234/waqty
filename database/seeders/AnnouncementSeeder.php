<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Announcement;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::first();

        $announcements = [
            [
                'title_en'   => 'Scheduled Maintenance',
                'title_ar'   => 'صيانة مجدولة',
                'message_en' => 'We will be performing maintenance on April 30 from 2–4 AM.',
                'message_ar' => 'سيتم إجراء صيانة في 30 أبريل من الساعة 2 إلى 4 صباحاً.',
                'target'     => 'all',
                'priority'   => 'high',
                'active'     => true,
                'ends_at'    => now()->addDays(7),
            ],
            [
                'title_en'   => 'New Features Available',
                'title_ar'   => 'ميزات جديدة متاحة',
                'message_en' => 'Check out the exciting new booking and review features now live!',
                'message_ar' => 'اكتشف ميزات الحجز والتقييم الجديدة المثيرة المتاحة الآن!',
                'target'     => 'users',
                'priority'   => 'normal',
                'active'     => true,
                'ends_at'    => null,
            ],
            [
                'title_en'   => 'Commission Rate Update',
                'title_ar'   => 'تحديث نسبة العمولة',
                'message_en' => 'Effective May 1, commission rates will be adjusted for all providers.',
                'message_ar' => 'اعتباراً من 1 مايو، سيتم تعديل نسب العمولة لجميع مزودي الخدمة.',
                'target'     => 'providers',
                'priority'   => 'urgent',
                'active'     => true,
                'ends_at'    => now()->addDays(30),
            ],
        ];

        foreach ($announcements as $data) {
            Announcement::create(array_merge($data, [
                'created_by_admin_id' => $admin?->id,
            ]));
        }
    }
}
