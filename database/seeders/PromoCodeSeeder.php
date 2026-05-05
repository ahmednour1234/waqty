<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\PromoCode;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::first();

        $codes = [
            [
                'code'         => 'SUMMER50',
                'type'         => PromoCode::TYPE_PERCENTAGE,
                'value'        => 50.00,
                'min_order'    => 100.00,
                'max_discount' => 200.00,
                'usage_limit'  => 500,
                'valid_until'  => '2026-08-31',
                'active'       => true,
            ],
            [
                'code'         => 'WELCOME20',
                'type'         => PromoCode::TYPE_PERCENTAGE,
                'value'        => 20.00,
                'min_order'    => 0.00,
                'max_discount' => null,
                'usage_limit'  => null,
                'valid_until'  => '2026-12-31',
                'active'       => true,
            ],
            [
                'code'         => 'FLAT30',
                'type'         => PromoCode::TYPE_FIXED,
                'value'        => 30.00,
                'min_order'    => 150.00,
                'max_discount' => null,
                'usage_limit'  => 200,
                'valid_until'  => '2026-07-01',
                'active'       => true,
            ],
            [
                'code'         => 'EXPIRED10',
                'type'         => PromoCode::TYPE_PERCENTAGE,
                'value'        => 10.00,
                'min_order'    => 0.00,
                'max_discount' => null,
                'usage_limit'  => 100,
                'valid_until'  => '2025-12-31',
                'active'       => false,
            ],
        ];

        foreach ($codes as $data) {
            PromoCode::firstOrCreate(
                ['code' => $data['code']],
                array_merge($data, ['created_by_admin_id' => $admin?->id])
            );
        }
    }
}
