<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\Shift;
use App\Models\ShiftDate;
use App\Models\ShiftTemplate;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ShiftDateSeeder extends Seeder
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

        $shifts = Shift::where('provider_id', $provider->id)->with('template')->get();

        if ($shifts->isEmpty()) {
            if ($this->command) {
                $this->command->warn('No shifts found. Please run ShiftSeeder first.');
            }
            return;
        }

        // Create 7 days of shift dates starting from today for each shift
        $today = Carbon::today();
        $count = 0;

        foreach ($shifts as $shift) {
            // Resolve times from the shift template or use defaults
            $template   = $shift->template;
            $startTime  = $template ? $template->start_time  : '08:00:00';
            $endTime    = $template ? $template->end_time    : '16:00:00';
            $breakStart = $template ? $template->break_start : null;
            $breakEnd   = $template ? $template->break_end   : null;

            for ($day = 0; $day < 7; $day++) {
                $date = $today->copy()->addDays($day)->toDateString();

                $existing = ShiftDate::where('shift_id', $shift->id)
                    ->where('shift_date', $date)
                    ->first();

                if (!$existing) {
                    ShiftDate::create([
                        'shift_id'    => $shift->id,
                        'shift_date'  => $date,
                        'start_time'  => $startTime,
                        'end_time'    => $endTime,
                        'break_start' => $breakStart,
                        'break_end'   => $breakEnd,
                        'active'      => true,
                    ]);
                    $count++;
                }
            }
        }

        if ($this->command) {
            $this->command->info("Seeded {$count} shift dates (7 days × {$shifts->count()} shifts).");
        }
    }
}
