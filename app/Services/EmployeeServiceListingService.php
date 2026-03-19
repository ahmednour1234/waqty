<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Service;
use App\Models\Subcategory;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EmployeeServiceListingService
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository
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

        if ($service->provider_id !== $employee->provider_id) {
            throw new \Illuminate\Auth\Access\AuthorizationException('api.services.unauthorized');
        }

        if (!$service->active) {
            throw new ModelNotFoundException('Service not found');
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
        return $filters;
    }
}
