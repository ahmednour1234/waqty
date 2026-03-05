<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Provider;
use App\Models\ProviderBranch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
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
        
        if (!$branch) {
            if ($this->command) {
                $this->command->warn('Provider branch not found. Please run ProviderBranchSeeder first.');
            }
            return;
        }

        $employee = Employee::where('email', 'employee@example.com')->first();
        
        if ($employee) {
            $employee->update([
                'provider_id' => $provider->id,
                'branch_id' => $branch->id,
                'name' => 'Test Employee',
                'password' => Hash::make('Employee@12345'),
                'phone' => '+201234567892',
                'active' => true,
                'blocked' => false,
            ]);
        } else {
            Employee::create([
                'provider_id' => $provider->id,
                'branch_id' => $branch->id,
                'name' => 'Test Employee',
                'email' => 'employee@example.com',
                'password' => Hash::make('Employee@12345'),
                'phone' => '+201234567892',
                'active' => true,
                'blocked' => false,
            ]);
        }

        if ($this->command) {
            $this->command->info('Seeded employee: employee@example.com (password: Employee@12345)');
        }
    }
}
