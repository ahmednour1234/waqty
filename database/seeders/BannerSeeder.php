<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::first();

        $banners = [
            [
                'title'      => 'Summer Sale Banner',
                'placement'  => Banner::PLACEMENT_HOME_TOP,
                'dimensions' => '1200x400',
                'active'     => true,
                'sort_order' => 1,
                'starts_at'  => '2026-06-01',
                'ends_at'    => '2026-08-31',
            ],
            [
                'title'      => 'App Update Banner',
                'placement'  => Banner::PLACEMENT_HOME_MIDDLE,
                'dimensions' => '1200x400',
                'active'     => true,
                'sort_order' => 2,
                'starts_at'  => null,
                'ends_at'    => null,
            ],
            [
                'title'      => 'New Provider Welcome',
                'placement'  => Banner::PLACEMENT_HOME_BOTTOM,
                'dimensions' => '800x400',
                'active'     => false,
                'sort_order' => 3,
                'starts_at'  => '2026-07-01',
                'ends_at'    => '2026-07-31',
            ],
        ];

        foreach ($banners as $data) {
            Banner::firstOrCreate(
                ['title' => $data['title'], 'placement' => $data['placement']],
                array_merge($data, ['created_by_admin_id' => $admin?->id])
            );
        }
    }
}
