<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\ShiftTemplate;
use Illuminate\Database\Seeder;

class ShiftTemplateSeeder extends Seeder
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

        $templates = [
            [
                'name'        => ['ar' => 'وردية الصباح', 'en' => 'Morning Shift'],
                'start_time'  => '07:00:00',
                'end_time'    => '15:00:00',
                'break_start' => '11:00:00',
                'break_end'   => '11:30:00',
                'active'      => true,
            ],
            [
                'name'        => ['ar' => 'وردية المساء', 'en' => 'Evening Shift'],
                'start_time'  => '15:00:00',
                'end_time'    => '23:00:00',
                'break_start' => '19:00:00',
                'break_end'   => '19:30:00',
                'active'      => true,
            ],
            [
                'name'        => ['ar' => 'وردية الليل', 'en' => 'Night Shift'],
                'start_time'  => '23:00:00',
                'end_time'    => '07:00:00',
                'break_start' => '03:00:00',
                'break_end'   => '03:30:00',
                'active'      => true,
            ],
        ];

        $count = 0;
        foreach ($templates as $templateData) {
            $existing = ShiftTemplate::where('provider_id', $provider->id)
                ->where('name->en', $templateData['name']['en'])
                ->first();

            if (!$existing) {
                ShiftTemplate::create(array_merge($templateData, ['provider_id' => $provider->id]));
                $count++;
            }
        }

        if ($this->command) {
            $this->command->info("Seeded {$count} shift templates.");
        }
    }
}
