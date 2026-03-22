<?php

namespace App\Services;

use App\Models\ServicePrice;
use App\Models\PricingGroup;
use App\Models\Service;
use App\Models\Employee;
use App\Models\ProviderBranch;
use App\Repositories\Contracts\ServicePriceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ProviderServicePricingService
{
    public function __construct(
        private ServicePriceRepositoryInterface $priceRepository,
    ) {}

    public function index(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $provider = $this->authenticatedProvider();
        $resolvedFilters = $this->resolveFilterIds($filters, $provider->id);

        return $this->priceRepository->paginateProvider($provider->id, $resolvedFilters, $perPage);
    }

    public function show(string $uuid): ServicePrice
    {
        $provider = $this->authenticatedProvider();
        $price = $this->priceRepository->findByUuidAndProvider($uuid, $provider->id);

        if (!$price) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('api.service_prices.not_found');
        }

        return $price;
    }

    public function store(array $data): ServicePrice
    {
        $provider = $this->authenticatedProvider();

        // Resolve service
        $service = Service::whereUuid($data['service_uuid'])->whereNull('deleted_at')->first();
        if (!$service) {
            throw new \InvalidArgumentException('api.services.not_found');
        }

        // Ensure service belongs to provider (via pivot)
        if (!$service->providers()->where('providers.id', $provider->id)->whereNull('provider_service.deleted_at')->exists()) {
            throw new \InvalidArgumentException('api.service_prices.service_not_owned');
        }

        $createData = [
            'provider_id' => $provider->id,
            'service_id'  => $service->id,
            'price'       => $data['price'],
            'active'      => $data['active'] ?? true,
        ];

        [$createData, $scopeType] = $this->resolveScope($data, $provider->id, $service->id, $createData);

        $this->assertNoDuplicate($service->id, $provider->id, $scopeType, $createData);

        return $this->priceRepository->create($createData);
    }

    public function update(string $uuid, array $data): ServicePrice
    {
        $provider = $this->authenticatedProvider();
        $price = $this->priceRepository->findByUuidAndProvider($uuid, $provider->id);

        if (!$price) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('api.service_prices.not_found');
        }

        $updateData = [];

        if (array_key_exists('price', $data)) {
            $updateData['price'] = $data['price'];
        }

        if (array_key_exists('active', $data)) {
            $updateData['active'] = $data['active'];
        }

        // Allow scope update — force all three nullable fields to be explicit
        if (array_key_exists('branch_uuid', $data) || array_key_exists('employee_uuid', $data) || array_key_exists('pricing_group_uuid', $data)) {
            $updateData['branch_id']         = null;
            $updateData['employee_id']       = null;
            $updateData['pricing_group_id']  = null;

            [$updateData, $scopeType] = $this->resolveScope($data, $provider->id, $price->service_id, $updateData);
            $this->assertNoDuplicate($price->service_id, $provider->id, $scopeType, $updateData, $price->id);
        }

        return $this->priceRepository->update($price, $updateData);
    }

    public function destroy(string $uuid): void
    {
        $provider = $this->authenticatedProvider();
        $price = $this->priceRepository->findByUuidAndProvider($uuid, $provider->id);

        if (!$price) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('api.service_prices.not_found');
        }

        $this->priceRepository->softDelete($price);
    }

    public function toggleActive(string $uuid): ServicePrice
    {
        $provider = $this->authenticatedProvider();
        $price = $this->priceRepository->findByUuidAndProvider($uuid, $provider->id);

        if (!$price) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('api.service_prices.not_found');
        }

        return $this->priceRepository->toggleActive($price, !$price->active);
    }

    private function resolveScope(array $data, int $providerId, int $serviceId, array $carry): array
    {
        $scopeType = 'default';

        if (!empty($data['employee_uuid'])) {
            $employee = Employee::whereUuid($data['employee_uuid'])
                ->where('provider_id', $providerId)
                ->whereNull('deleted_at')
                ->first();
            if (!$employee) {
                throw new \InvalidArgumentException('api.employees.not_found');
            }
            $carry['employee_id'] = $employee->id;
            $scopeType = 'employee';

        } elseif (!empty($data['pricing_group_uuid'])) {
            $group = PricingGroup::whereUuid($data['pricing_group_uuid'])
                ->where('provider_id', $providerId)
                ->whereNull('deleted_at')
                ->first();
            if (!$group) {
                throw new \InvalidArgumentException('api.pricing_groups.not_found');
            }
            $carry['pricing_group_id'] = $group->id;
            $scopeType = 'group';

        } elseif (!empty($data['branch_uuid'])) {
            $branch = ProviderBranch::whereUuid($data['branch_uuid'])
                ->where('provider_id', $providerId)
                ->whereNull('deleted_at')
                ->first();
            if (!$branch) {
                throw new \InvalidArgumentException('api.branches.not_found');
            }
            $carry['branch_id'] = $branch->id;
            $scopeType = 'branch';
        }

        return [$carry, $scopeType];
    }

    private function assertNoDuplicate(int $serviceId, int $providerId, string $scopeType, array $data, ?int $excludeId = null): void
    {
        $exists = match ($scopeType) {
            'default'  => $this->priceRepository->existsDefault($serviceId, $providerId, $excludeId),
            'branch'   => $this->priceRepository->existsBranch($serviceId, $data['branch_id'], $providerId, $excludeId),
            'employee' => $this->priceRepository->existsEmployee($serviceId, $data['employee_id'], $providerId, $excludeId),
            'group'    => $this->priceRepository->existsGroup($serviceId, $data['pricing_group_id'], $providerId, $excludeId),
        };

        if ($exists) {
            throw new \InvalidArgumentException('api.service_prices.duplicate_scope');
        }
    }

    private function resolveFilterIds(array $filters, int $providerId): array
    {
        if (!empty($filters['service_uuid'])) {
            $service = Service::whereUuid($filters['service_uuid'])->first();
            $filters['service_id'] = $service?->id;
        }

        if (!empty($filters['sub_category_uuid'])) {
            $sub = \App\Models\Subcategory::whereUuid($filters['sub_category_uuid'])->first();
            $filters['sub_category_id'] = $sub?->id;
        }

        if (!empty($filters['branch_uuid'])) {
            $branch = ProviderBranch::whereUuid($filters['branch_uuid'])->where('provider_id', $providerId)->first();
            $filters['branch_id'] = $branch?->id;
        }

        if (!empty($filters['employee_uuid'])) {
            $employee = Employee::whereUuid($filters['employee_uuid'])->where('provider_id', $providerId)->first();
            $filters['employee_id'] = $employee?->id;
        }

        if (!empty($filters['pricing_group_uuid'])) {
            $group = PricingGroup::whereUuid($filters['pricing_group_uuid'])->where('provider_id', $providerId)->first();
            $filters['pricing_group_id'] = $group?->id;
        }

        return $filters;
    }

    private function authenticatedProvider()
    {
        return Auth::guard('provider')->user();
    }
}
