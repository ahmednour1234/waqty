<?php

namespace App\Services;

use App\Models\PricingGroup;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Repositories\Contracts\PricingGroupEmployeeRepositoryInterface;
use App\Repositories\Contracts\ServicePriceRepositoryInterface;
use App\Models\ProviderBranch;
use App\Models\Employee;

/**
 * Resolves the final price for a service given optional context (employee, branch).
 *
 * Priority:
 *  1. employee-specific price
 *  2. pricing-group-specific price (if employee belongs to an active group with a group price)
 *  3. branch-specific price
 *  4. default/global price
 */
class PriceResolverService
{
    public function __construct(
        private ServicePriceRepositoryInterface         $priceRepository,
        private PricingGroupEmployeeRepositoryInterface $groupEmployeeRepository,
    ) {}

    /**
     * @return array|null  [final_price, source_type, source_uuid, service_uuid, provider_uuid]
     */
    public function getPrice(int $serviceId, ?int $employeeId = null, ?int $branchId = null): ?array
    {
        // 1. Employee-specific price
        if ($employeeId !== null) {
            $price = ServicePrice::where('service_id', $serviceId)
                ->where('employee_id', $employeeId)
                ->where('active', true)
                ->whereNull('deleted_at')
                ->with(['service', 'provider', 'employee'])
                ->first();

            if ($price) {
                return $this->buildResult($price, 'employee', $price->employee?->uuid);
            }

            // 2. Pricing-group-specific price
            $groupIds = $this->groupEmployeeRepository->getGroupIdsForEmployee($employeeId);

            if (!empty($groupIds)) {
                $price = ServicePrice::where('service_id', $serviceId)
                    ->whereIn('pricing_group_id', $groupIds)
                    ->where('active', true)
                    ->whereNull('deleted_at')
                    ->whereHas('pricingGroup', fn ($q) => $q->where('active', true)->whereNull('deleted_at'))
                    ->with(['service', 'provider', 'pricingGroup'])
                    ->first();

                if ($price) {
                    return $this->buildResult($price, 'group', $price->pricingGroup?->uuid);
                }
            }
        }

        // 3. Branch-specific price
        if ($branchId !== null) {
            $price = ServicePrice::where('service_id', $serviceId)
                ->where('branch_id', $branchId)
                ->where('active', true)
                ->whereNull('deleted_at')
                ->with(['service', 'provider', 'branch'])
                ->first();

            if ($price) {
                return $this->buildResult($price, 'branch', $price->branch?->uuid);
            }
        }

        // 4. Default/global price
        $price = ServicePrice::where('service_id', $serviceId)
            ->whereNull('branch_id')
            ->whereNull('employee_id')
            ->whereNull('pricing_group_id')
            ->where('active', true)
            ->whereNull('deleted_at')
            ->with(['service', 'provider'])
            ->first();

        if ($price) {
            return $this->buildResult($price, 'default', null);
        }

        return null;
    }

    /**
     * Resolve context UUIDs to IDs and delegate to getPrice().
     */
    public function getPriceByUuids(string $serviceUuid, ?string $employeeUuid = null, ?string $branchUuid = null): ?array
    {
        $service = Service::whereUuid($serviceUuid)->where('active', true)->whereNull('deleted_at')->first();
        if (!$service) {
            return null;
        }

        $employeeId = null;
        if ($employeeUuid !== null) {
            $employee = Employee::whereUuid($employeeUuid)->whereNull('deleted_at')->first();
            $employeeId = $employee?->id;
        }

        $branchId = null;
        if ($branchUuid !== null) {
            $branch = ProviderBranch::whereUuid($branchUuid)->whereNull('deleted_at')->first();
            $branchId = $branch?->id;
        }

        return $this->getPrice($service->id, $employeeId, $branchId);
    }

    private function buildResult(ServicePrice $price, string $sourceType, ?string $sourceUuid): array
    {
        $price->loadMissing(['service', 'provider']);

        return [
            'final_price'  => $price->price,
            'source_type'  => $sourceType,
            'source_uuid'  => $sourceUuid,
            'service_uuid' => $price->service?->uuid,
            'provider_uuid'=> $price->provider?->uuid,
            'price_uuid'   => $price->uuid,
        ];
    }
}
