<?php

namespace App\Services;

use App\Models\Subcategory;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminServiceService
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository
    ) {}

    public function index(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $resolved = $this->resolveFilterUuids($filters);
        return $this->serviceRepository->paginateAdmin($resolved, $perPage);
    }

    public function show(string $uuid): \App\Models\Service
    {
        $service = $this->serviceRepository->findByUuid($uuid);
        if (!$service) {
            throw new ModelNotFoundException('Service not found');
        }
        return $service;
    }

    public function updateStatus(string $uuid, bool $active): \App\Models\Service
    {
        $service = $this->serviceRepository->findByUuid($uuid);
        if (!$service) {
            throw new ModelNotFoundException('Service not found');
        }
        return $this->serviceRepository->toggleActive($service, $active);
    }

    public function destroy(string $uuid): void
    {
        $service = $this->serviceRepository->findByUuid($uuid);
        if (!$service) {
            throw new ModelNotFoundException('Service not found');
        }
        $this->serviceRepository->softDelete($service);
    }

    public function restore(string $uuid): \App\Models\Service
    {
        $service = $this->serviceRepository->restore($uuid);
        if (!$service) {
            throw new ModelNotFoundException('Service not found');
        }
        return $service;
    }

    private function resolveFilterUuids(array $filters): array
    {
        if (!empty($filters['provider_uuid'])) {
            $provider = \App\Models\Provider::whereUuid($filters['provider_uuid'])->first();
            $filters['provider_id'] = $provider ? $provider->id : null;
            unset($filters['provider_uuid']);
        }

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
}
