<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\ProviderBranch;
use App\Models\Shift;
use App\Models\ShiftTemplate;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
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

        $branch = ProviderBranch::where('provider_id', $provider->id)
            ->where('is_main', true)
            ->first();

        $morningTemplate = ShiftTemplate::where('provider_id', $provider->id)
            ->where('name->en', 'Morning Shift')
            ->first();

        $eveningTemplate = ShiftTemplate::where('provider_id', $provider->id)
            ->where('name->en', 'Evening Shift')
            ->first();

        $nightTemplate = ShiftTemplate::where('provider_id', $provider->id)
            ->where('name->en', 'Night Shift')
            ->first();

        if (!$morningTemplate) {
            if ($this->command) {
                $this->command->warn('Shift templates not found. Please run ShiftTemplateSeeder first.');
            }
            return;
        }

        $shifts = [
            [
                'title'             => 'Morning Shift – Week 1',
                'notes'             => 'Regular morning coverage',
                'shift_template_id' => $morningTemplate->id,
                'active'            => true,
            ],
            [
                'title'             => 'Evening Shift – Week 1',
                'notes'             => 'Regular evening coverage',
                'shift_template_id' => $eveningTemplate ? $eveningTemplate->id : null,
                'active'            => true,
            ],
            [
                'title'             => 'Night Shift – Week 1',
                'notes'             => 'Overnight coverage',
                'shift_template_id' => $nightTemplate ? $nightTemplate->id : null,
                'active'            => true,
            ],
        ];

        $count = 0;
        foreach ($shifts as $shiftData) {
            $existing = Shift::where('provider_id', $provider->id)
                ->where('title', $shiftData['title'])
                ->first();

            if (!$existing) {
                Shift::create(array_merge($shiftData, [
                    'provider_id'      => $provider->id,
                    'branch_id'        => $branch ? $branch->id : null,
                    'created_by_type'  => 'admin',
                    'created_by_id'    => 1,
                ]));
                $count++;
            }
        }

        if ($this->command) {
            $this->command->info("Seeded {$count} shifts.");
        }
    }
}
