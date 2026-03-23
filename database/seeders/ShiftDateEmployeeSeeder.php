<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Provider;
use App\Models\Shift;
use App\Models\ShiftDate;
use App\Models\ShiftDateEmployee;
use Illuminate\Database\Seeder;

class ShiftDateEmployeeSeeder extends Seeder
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

        $employees = Employee::where('provider_id', $provider->id)->get();

        if ($employees->isEmpty()) {
            if ($this->command) {
                $this->command->warn('No employees found. Please run EmployeeSeeder first.');
            }
            return;
        }

        $shiftIds = Shift::where('provider_id', $provider->id)->pluck('id');

        if ($shiftIds->isEmpty()) {
            if ($this->command) {
                $this->command->warn('No shifts found. Please run ShiftSeeder first.');
            }
            return;
        }

        $shiftDates = ShiftDate::whereIn('shift_id', $shiftIds)->get();

        if ($shiftDates->isEmpty()) {
            if ($this->command) {
                $this->command->warn('No shift dates found. Please run ShiftDateSeeder first.');
            }
            return;
        }

        $count = 0;
        foreach ($shiftDates as $shiftDate) {
            foreach ($employees as $employee) {
                $existing = ShiftDateEmployee::where('shift_date_id', $shiftDate->id)
                    ->where('employee_id', $employee->id)
                    ->first();

                if (!$existing) {
                    ShiftDateEmployee::create([
                        'shift_date_id' => $shiftDate->id,
                        'employee_id'   => $employee->id,
                        'assigned_at'   => now(),
                    ]);
                    $count++;
                }
            }
        }

        if ($this->command) {
            $this->command->info("Assigned {$count} employee-shift-date records.");
        }
    }
}
