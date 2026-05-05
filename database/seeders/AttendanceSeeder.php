<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Provider;
use App\Models\ShiftDate;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $employee = Employee::where('email', 'employee@example.com')->first();

        if (!$employee) {
            if ($this->command) {
                $this->command->warn('Employee not found. Please run EmployeeSeeder first.');
            }
            return;
        }

        // Get past shift dates that have this employee assigned via shift_date_employees
        $shiftDateIds = \DB::table('shift_date_employees')
            ->where('employee_id', $employee->id)
            ->pluck('shift_date_id')
            ->toArray();

        if (empty($shiftDateIds)) {
            if ($this->command) {
                $this->command->warn('No shift date employees found. Please run ShiftDateEmployeeSeeder first.');
            }
            return;
        }

        // Only pick past/today shift dates
        $shiftDates = ShiftDate::whereIn('id', $shiftDateIds)
            ->where('shift_date', '<=', Carbon::today()->toDateString())
            ->orderBy('shift_date')
            ->get();

        if ($shiftDates->isEmpty()) {
            if ($this->command) {
                $this->command->warn('No past shift dates found for attendance seeding.');
            }
            return;
        }

        $count = 0;

        foreach ($shiftDates as $shiftDate) {
            $exists = Attendance::where('employee_id', $employee->id)
                ->where('shift_date_id', $shiftDate->id)
                ->exists();

            if ($exists) {
                continue;
            }

            $checkIn  = Carbon::parse("{$shiftDate->shift_date} {$shiftDate->start_time}");
            $checkOut = Carbon::parse("{$shiftDate->shift_date} {$shiftDate->end_time}");

            Attendance::create([
                'employee_id'      => $employee->id,
                'shift_date_id'    => $shiftDate->id,
                'check_in_at'      => $checkIn,
                'check_out_at'     => $checkOut,
                'working_minutes'  => $checkIn->diffInMinutes($checkOut),
                'notes'            => null,
            ]);

            $count++;
        }

        if ($this->command) {
            $this->command->info("Seeded {$count} attendance records.");
        }
    }
}
