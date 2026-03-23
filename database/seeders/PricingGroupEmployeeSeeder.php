<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\PricingGroup;
use App\Models\PricingGroupEmployee;
use App\Models\Provider;
use Illuminate\Database\Seeder;

class PricingGroupEmployeeSeeder extends Seeder
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

        // Assign each employee to the "Basic Group"
        $basicGroup = PricingGroup::where('provider_id', $provider->id)
            ->where('name->en', 'Basic Group')
            ->first();

        if (!$basicGroup) {
            if ($this->command) {
                $this->command->warn('Pricing groups not found. Please run PricingGroupSeeder first.');
            }
            return;
        }

        $count = 0;
        foreach ($employees as $employee) {
            $existing = PricingGroupEmployee::where('pricing_group_id', $basicGroup->id)
                ->where('employee_id', $employee->id)
                ->first();

            if (!$existing) {
                PricingGroupEmployee::create([
                    'pricing_group_id' => $basicGroup->id,
                    'employee_id'      => $employee->id,
                ]);
                $count++;
            }
        }

        if ($this->command) {
            $this->command->info("Assigned {$count} employees to pricing groups.");
        }
    }
}
