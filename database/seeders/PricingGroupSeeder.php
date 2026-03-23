<?php

namespace Database\Seeders;

use App\Models\PricingGroup;
use App\Models\Provider;
use Illuminate\Database\Seeder;

class PricingGroupSeeder extends Seeder
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

        $groups = [
            [
                'name'   => ['ar' => 'المجموعة الأساسية', 'en' => 'Basic Group'],
                'active' => true,
            ],
            [
                'name'   => ['ar' => 'مجموعة العمل الإضافي', 'en' => 'Overtime Group'],
                'active' => true,
            ],
            [
                'name'   => ['ar' => 'مجموعة موظفي الخبرة', 'en' => 'Senior Staff Group'],
                'active' => true,
            ],
        ];

        $count = 0;
        foreach ($groups as $groupData) {
            $existing = PricingGroup::where('provider_id', $provider->id)
                ->where('name->en', $groupData['name']['en'])
                ->first();

            if (!$existing) {
                PricingGroup::create(array_merge($groupData, ['provider_id' => $provider->id]));
                $count++;
            }
        }

        if ($this->command) {
            $this->command->info("Seeded {$count} pricing groups.");
        }
    }
}
