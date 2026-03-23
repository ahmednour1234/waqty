<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProviderServiceSeeder extends Seeder
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

        $services = Service::all();

        if ($services->isEmpty()) {
            if ($this->command) {
                $this->command->warn('No services found. Please run ServiceSeeder first.');
            }
            return;
        }

        $count = 0;
        foreach ($services as $service) {
            $exists = DB::table('provider_service')
                ->where('provider_id', $provider->id)
                ->where('service_id', $service->id)
                ->exists();

            if (!$exists) {
                DB::table('provider_service')->insert([
                    'provider_id'                => $provider->id,
                    'service_id'                 => $service->id,
                    'active'                     => true,
                    'estimated_duration_minutes' => 30,
                    'created_at'                 => now(),
                    'updated_at'                 => now(),
                ]);
                $count++;
            }
        }

        if ($this->command) {
            $this->command->info("Assigned {$count} services to provider.");
        }
    }
}
