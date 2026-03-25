<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PricingGroup;
use App\Models\Service;
use App\Models\Subcategory;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EmployeeServiceListingService
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
        private PriceResolverService $priceResolver,
    ) {}

    public function index(Employee $employee, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $resolved = $this->resolveSubCategoryFilter($filters);
        return $this->serviceRepository->paginateEmployee($employee->provider_id, $resolved, $perPage);
    }

    public function show(Employee $employee, string $uuid): Service
    {
        $service = $this->serviceRepository->findByUuid($uuid);

        if (!$service) {
            throw new ModelNotFoundException('Service not found');
        }

        if (!$this->serviceRepository->isAttachedToProvider($service, $employee->provider_id)) {
            throw new \Illuminate\Auth\Access\AuthorizationException('api.services.unauthorized');
        }

        return $service;
    }

    private function resolveSubCategoryFilter(array $filters): array
    {
        if (!empty($filters['sub_category_uuid'])) {
            $sub = Subcategory::whereUuid($filters['sub_category_uuid'])->first();
            $filters['sub_category_id'] = $sub ? $sub->id : null;
            unset($filters['sub_category_uuid']);
        }

        if (!empty($filters['category']) && empty($filters['sub_category_id'])) {
            $category = trim((string) $filters['category']);
            $sub = Subcategory::whereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$category}%"])
                ->orWhereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$category}%"])
                ->first();

            $filters['sub_category_id'] = $sub ? $sub->id : null;
        }

        unset($filters['category']);

        return $filters;
    }

    /**
     * Return paginated services with the employee's effective price for each service.
     *
     * @return array{paginator: LengthAwarePaginator, items: array<int, array{service: Service, pricing: array|null}>}
     */
    public function indexWithPrices(Employee $employee, array $filters, int $perPage = 15): array
    {
        $resolved  = $this->resolveSubCategoryFilter($filters);
        $paginator = $this->serviceRepository->paginateEmployee($employee->provider_id, $resolved, $perPage);

        $items = array_map(function (Service $service) use ($employee) {
            $pricing = $this->priceResolver->getPrice($service->id, $employee->id, $employee->branch_id);

            $pricingGroup = null;
            if ($pricing && $pricing['source_type'] === 'group' && !empty($pricing['source_uuid'])) {
                $group = PricingGroup::whereUuid($pricing['source_uuid'])->first();
                if ($group) {
                    $locale       = app()->getLocale();
                    $pricingGroup = [
                        'uuid' => $group->uuid,
                        'name' => $group->name[$locale] ?? $group->name['ar'] ?? null,
                    ];
                }
            }

            return [
                'service' => $service,
                'pricing' => $pricing !== null
                    ? array_merge($pricing, ['pricing_group' => $pricingGroup])
                    : null,
            ];
        }, $paginator->items());

        return ['paginator' => $paginator, 'items' => $items];
    }
}
