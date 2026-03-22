<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\ProviderBranch;
use App\Models\Service;
use App\Models\Subcategory;
use App\Repositories\Contracts\PricingGroupRepositoryInterface;
use App\Repositories\Contracts\ServicePriceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminServicePricingReadService
{
    public function __construct(
        private ServicePriceRepositoryInterface  $priceRepository,
        private PricingGroupRepositoryInterface  $groupRepository,
    ) {}

    public function listPrices(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $resolvedFilters = $this->resolveFilterIds($filters);
        return $this->priceRepository->paginateAdmin($resolvedFilters, $perPage);
    }

    public function showPrice(string $uuid): \App\Models\ServicePrice
    {
        $price = $this->priceRepository->findByUuid($uuid);

        if (!$price) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('api.service_prices.not_found');
        }

        return $price;
    }

    public function listGroups(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $resolvedFilters = $this->resolveGroupFilterIds($filters);
        return $this->groupRepository->paginateAdmin($resolvedFilters, $perPage);
    }

    public function showGroup(string $uuid): \App\Models\PricingGroup
    {
        $group = $this->groupRepository->findByUuid($uuid);

        if (!$group) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('api.pricing_groups.not_found');
        }

        return $group;
    }

    private function resolveFilterIds(array $filters): array
    {
        if (!empty($filters['provider_uuid'])) {
            $provider = \App\Models\Provider::whereUuid($filters['provider_uuid'])->first();
            $filters['provider_id'] = $provider?->id;
        }

        if (!empty($filters['service_uuid'])) {
            $service = Service::whereUuid($filters['service_uuid'])->first();
            $filters['service_id'] = $service?->id;
        }

        if (!empty($filters['sub_category_uuid'])) {
            $sub = Subcategory::whereUuid($filters['sub_category_uuid'])->first();
            $filters['sub_category_id'] = $sub?->id;
        }

        if (!empty($filters['branch_uuid'])) {
            $branch = ProviderBranch::whereUuid($filters['branch_uuid'])->first();
            $filters['branch_id'] = $branch?->id;
        }

        if (!empty($filters['employee_uuid'])) {
            $employee = Employee::whereUuid($filters['employee_uuid'])->first();
            $filters['employee_id'] = $employee?->id;
        }

        if (!empty($filters['pricing_group_uuid'])) {
            $group = \App\Models\PricingGroup::whereUuid($filters['pricing_group_uuid'])->first();
            $filters['pricing_group_id'] = $group?->id;
        }

        return $filters;
    }

    private function resolveGroupFilterIds(array $filters): array
    {
        if (!empty($filters['provider_uuid'])) {
            $provider = \App\Models\Provider::whereUuid($filters['provider_uuid'])->first();
            $filters['provider_id'] = $provider?->id;
        }

        return $filters;
    }
}
