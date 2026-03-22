<?php

namespace App\Repositories;

use App\Models\ServicePrice;
use App\Repositories\Contracts\ServicePriceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ServicePriceRepository implements ServicePriceRepositoryInterface
{
    public function findByUuid(string $uuid): ?ServicePrice
    {
        return ServicePrice::whereUuid($uuid)
            ->with(['provider', 'service', 'branch', 'employee', 'pricingGroup'])
            ->first();
    }

    public function findByUuidAndProvider(string $uuid, int $providerId): ?ServicePrice
    {
        return ServicePrice::whereUuid($uuid)
            ->where('provider_id', $providerId)
            ->with(['service', 'branch', 'employee', 'pricingGroup'])
            ->first();
    }

    public function paginateProvider(int $providerId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = ServicePrice::with(['service', 'branch', 'employee', 'pricingGroup'])
            ->where('provider_id', $providerId);

        $this->applyCommonFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function paginateAdmin(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = ServicePrice::with(['provider', 'service', 'branch', 'employee', 'pricingGroup']);

        if (!empty($filters['trashed'])) {
            if ($filters['trashed'] === 'only') {
                $query->onlyTrashed();
            } elseif ($filters['trashed'] === 'with') {
                $query->withTrashed();
            }
        }

        if (!empty($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        $this->applyCommonFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function existsDefault(int $serviceId, int $providerId, ?int $excludeId = null): bool
    {
        return ServicePrice::where('service_id', $serviceId)
            ->where('provider_id', $providerId)
            ->whereNull('branch_id')
            ->whereNull('employee_id')
            ->whereNull('pricing_group_id')
            ->when($excludeId !== null, fn ($q) => $q->where('id', '!=', $excludeId))
            ->whereNull('deleted_at')
            ->exists();
    }

    public function existsBranch(int $serviceId, int $branchId, int $providerId, ?int $excludeId = null): bool
    {
        return ServicePrice::where('service_id', $serviceId)
            ->where('branch_id', $branchId)
            ->where('provider_id', $providerId)
            ->when($excludeId !== null, fn ($q) => $q->where('id', '!=', $excludeId))
            ->whereNull('deleted_at')
            ->exists();
    }

    public function existsEmployee(int $serviceId, int $employeeId, int $providerId, ?int $excludeId = null): bool
    {
        return ServicePrice::where('service_id', $serviceId)
            ->where('employee_id', $employeeId)
            ->where('provider_id', $providerId)
            ->when($excludeId !== null, fn ($q) => $q->where('id', '!=', $excludeId))
            ->whereNull('deleted_at')
            ->exists();
    }

    public function existsGroup(int $serviceId, int $groupId, int $providerId, ?int $excludeId = null): bool
    {
        return ServicePrice::where('service_id', $serviceId)
            ->where('pricing_group_id', $groupId)
            ->where('provider_id', $providerId)
            ->when($excludeId !== null, fn ($q) => $q->where('id', '!=', $excludeId))
            ->whereNull('deleted_at')
            ->exists();
    }

    public function create(array $data): ServicePrice
    {
        return ServicePrice::create($data);
    }

    public function update(ServicePrice $price, array $data): ServicePrice
    {
        $price->update($data);
        return $price->fresh(['service', 'branch', 'employee', 'pricingGroup']);
    }

    public function softDelete(ServicePrice $price): bool
    {
        return $price->delete();
    }

    public function toggleActive(ServicePrice $price, bool $active): ServicePrice
    {
        $price->update(['active' => $active]);
        return $price;
    }

    private function applyCommonFilters($query, array $filters): void
    {
        if (!empty($filters['service_id'])) {
            $query->where('service_id', $filters['service_id']);
        }

        if (!empty($filters['sub_category_id'])) {
            $query->whereHas('service', fn ($q) => $q->where('sub_category_id', $filters['sub_category_id']));
        }

        if (!empty($filters['scope_type'])) {
            match ($filters['scope_type']) {
                'default'  => $query->whereNull('branch_id')->whereNull('employee_id')->whereNull('pricing_group_id'),
                'branch'   => $query->whereNotNull('branch_id'),
                'employee' => $query->whereNotNull('employee_id'),
                'group'    => $query->whereNotNull('pricing_group_id'),
                default    => null,
            };
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['pricing_group_id'])) {
            $query->where('pricing_group_id', $filters['pricing_group_id']);
        }

        if (isset($filters['active']) && $filters['active'] !== null) {
            $query->where('active', $filters['active']);
        }
    }
}
