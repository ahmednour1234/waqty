<?php

namespace App\Services;

use App\Models\Service;
use App\Models\Subcategory;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PublicServiceService
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository
    ) {}

    public function index(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $resolved = $this->resolveFilterUuids($filters);
        return $this->serviceRepository->paginatePublic($resolved, $perPage);
    }

    public function show(string $uuid): Service
    {
        $service = $this->serviceRepository->findByUuid($uuid);

        if (!$service) {
            throw new ModelNotFoundException('Service not found');
        }

        $hasValidProvider = $service->providers()
            ->wherePivotNull('deleted_at')
            ->wherePivot('active', true)
            ->where('providers.active', true)
            ->where('providers.blocked', false)
            ->where('providers.banned', false)
            ->whereNull('providers.deleted_at')
            ->exists();

        if (!$hasValidProvider) {
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

        return $filters;
    }
}
