<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
            ]
        );

        $this->call(AdminSeeder::class);
        $this->call(CountriesSeeder::class);
        $this->call(CitiesSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(SubcategorySeeder::class);
        $this->call(ProviderSeeder::class);
        $this->call(ProviderBranchSeeder::class);
        $this->call(EmployeeSeeder::class);

        // Services
        $this->call(ServiceSeeder::class);
        $this->call(ProviderServiceSeeder::class);

        // Shifts
        $this->call(ShiftTemplateSeeder::class);
        $this->call(ShiftSeeder::class);
        $this->call(ShiftDateSeeder::class);
        $this->call(ShiftDateEmployeeSeeder::class);

        // Pricing
        $this->call(PricingGroupSeeder::class);
        $this->call(PricingGroupEmployeeSeeder::class);
        $this->call(ServicePriceSeeder::class);
    }
}
