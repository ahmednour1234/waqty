<?php

namespace Database\Seeders;

use App\Models\PricingGroup;
use App\Models\Provider;
use App\Models\ProviderBranch;
use App\Models\Service;
use App\Models\ServicePrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicePriceSeeder extends Seeder
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

        // Only seed prices for services assigned to this provider
        $serviceIds = DB::table('provider_service')
            ->where('provider_id', $provider->id)
            ->pluck('service_id');

        if ($serviceIds->isEmpty()) {
            if ($this->command) {
                $this->command->warn('No provider services found. Please run ProviderServiceSeeder first.');
            }
            return;
        }

        $services = Service::whereIn('id', $serviceIds)->get();

        $branch = ProviderBranch::where('provider_id', $provider->id)
            ->where('is_main', true)
            ->first();

        $pricingGroup = PricingGroup::where('provider_id', $provider->id)
            ->where('name->en', 'Basic Group')
            ->first();

        // Base prices per service (in EGP)
        $basePrices = [
            'Mixed Grill'         => 150.00,
            'Chicken Kabsa'       => 85.00,
            'Fattoush Salad'      => 35.00,
            'Margherita Pizza'    => 95.00,
            'Pasta Carbonara'     => 80.00,
            'Arabic Coffee'       => 20.00,
            'Mint Tea'            => 15.00,
            'Latte'               => 30.00,
            'Cappuccino'          => 30.00,
            'Mojito'              => 25.00,
        ];

        $count = 0;
        foreach ($services as $service) {
            $englishName = $service->name['en'] ?? '';
            $basePrice   = $basePrices[$englishName] ?? 50.00;

            // 1) Provider-level default price (no branch / employee / group)
            $this->createIfNotExists($provider->id, $service->id, null, null, null, $basePrice, $count);

            // 2) Branch-specific price (5% discount)
            if ($branch) {
                $this->createIfNotExists($provider->id, $service->id, $branch->id, null, null, round($basePrice * 0.95, 2), $count);
            }

            // 3) Pricing-group price (10% discount)
            if ($pricingGroup) {
                $this->createIfNotExists($provider->id, $service->id, null, null, $pricingGroup->id, round($basePrice * 0.90, 2), $count);
            }
        }

        if ($this->command) {
            $this->command->info("Seeded {$count} service price records.");
        }
    }

    private function createIfNotExists(
        int  $providerId,
        int  $serviceId,
        ?int $branchId,
        ?int $employeeId,
        ?int $pricingGroupId,
        float $price,
        int  &$count
    ): void {
        $query = ServicePrice::where('provider_id', $providerId)
            ->where('service_id', $serviceId)
            ->where('active', true);

        $branchId       === null ? $query->whereNull('branch_id')        : $query->where('branch_id', $branchId);
        $employeeId     === null ? $query->whereNull('employee_id')      : $query->where('employee_id', $employeeId);
        $pricingGroupId === null ? $query->whereNull('pricing_group_id') : $query->where('pricing_group_id', $pricingGroupId);

        if (!$query->exists()) {
            ServicePrice::create([
                'provider_id'      => $providerId,
                'service_id'       => $serviceId,
                'branch_id'        => $branchId,
                'employee_id'      => $employeeId,
                'pricing_group_id' => $pricingGroupId,
                'price'            => $price,
                'active'           => true,
            ]);
            $count++;
        }
    }
}
