<?php

namespace App\Repositories;

use App\Models\Provider;
use App\Models\Service;
use App\Models\Subcategory;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ServiceRepository implements ServiceRepositoryInterface
{
    public function findByUuid(string $uuid): ?Service
    {
        return Service::whereUuid($uuid)->with(['providers', 'subCategory'])->first();
    }

    public function paginateAdmin(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Service::with(['providers', 'subCategory']);

        if (!empty($filters['trashed']) && $filters['trashed'] === 'only') {
            $query->onlyTrashed();
        } elseif (!empty($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        }

        $this->applyCommonFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function paginateProvider(int $providerId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Service::with(['subCategory'])
            ->whereHas('providers', fn ($q) => $q
                ->where('providers.id', $providerId)
                ->whereNull('provider_service.deleted_at')
            );

        $this->applyCommonFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function paginateEmployee(int $providerId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Service::with(['subCategory'])
            ->whereHas('providers', fn ($q) => $q
                ->where('providers.id', $providerId)
                ->whereNull('provider_service.deleted_at')
            );

        if (isset($filters['sub_category_id'])) {
            $query->where('sub_category_id', $filters['sub_category_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(description, '$.ar') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(description, '$.en') LIKE ?", ["%{$search}%"]);
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function paginatePublic(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Service::with(['providers', 'subCategory']);

        if (isset($filters['provider_id'])) {
            $providerId = $filters['provider_id'];
            $query->whereHas('providers', fn ($q) => $q
                ->where('providers.id', $providerId)
                ->whereNull('provider_service.deleted_at')
            );
        }

        if (isset($filters['sub_category_id'])) {
            $query->where('sub_category_id', $filters['sub_category_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(description, '$.ar') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(description, '$.en') LIKE ?", ["%{$search}%"]);
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Service
    {
        return Service::create($data);
    }

    public function update(Service $service, array $data): Service
    {
        $service->update($data);
        return $service->fresh(['providers', 'subCategory']);
    }

    public function softDelete(Service $service): bool
    {
        return $service->delete();
    }

    public function restore(string $uuid): ?Service
    {
        $service = Service::onlyTrashed()->whereUuid($uuid)->first();
        if ($service) {
            $service->restore();
            return $service->fresh(['providers', 'subCategory']);
        }
        return null;
    }

    public function attachProvider(Service $service, int $providerId): void
    {
        $service->providers()->attach($providerId, ['active' => true]);
    }

    public function softDeletePivot(Service $service, int $providerId): void
    {
        $service->providers()->updateExistingPivot($providerId, ['deleted_at' => now()]);
    }

    public function togglePivotActive(Service $service, int $providerId, bool $active): Service
    {
        $service->providers()->updateExistingPivot($providerId, ['active' => $active]);
        return $service->fresh(['providers', 'subCategory']);
    }

    public function isAttachedToProvider(Service $service, int $providerId): bool
    {
        return $service->providers()
            ->where('providers.id', $providerId)
            ->wherePivotNull('deleted_at')
            ->exists();
    }

    private function applyCommonFilters($query, array $filters): void
    {
        if (isset($filters['provider_id'])) {
            $providerId = $filters['provider_id'];
            $query->whereHas('providers', fn ($q) => $q
                ->where('providers.id', $providerId)
                ->whereNull('provider_service.deleted_at')
            );
        }

        if (isset($filters['sub_category_id'])) {
            $query->where('sub_category_id', $filters['sub_category_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(description, '$.ar') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(description, '$.en') LIKE ?", ["%{$search}%"]);
            });
        }
    }
}
