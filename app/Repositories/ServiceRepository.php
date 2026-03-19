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
        return Service::whereUuid($uuid)->with(['provider', 'subCategory'])->first();
    }

    public function paginateAdmin(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Service::with(['provider', 'subCategory']);

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
            ->where('provider_id', $providerId);

        $this->applyCommonFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function paginateEmployee(int $providerId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Service::with(['subCategory'])
            ->where('provider_id', $providerId);

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
        $query = Service::with(['provider', 'subCategory']);

        if (isset($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
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
        return $service->fresh(['provider', 'subCategory']);
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
            return $service->fresh(['provider', 'subCategory']);
        }
        return null;
    }

    public function toggleActive(Service $service, bool $active): Service
    {
        $service->update(['active' => $active]);
        return $service->fresh(['provider', 'subCategory']);
    }

    private function applyCommonFilters($query, array $filters): void
    {
        if (isset($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        if (isset($filters['sub_category_id'])) {
            $query->where('sub_category_id', $filters['sub_category_id']);
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
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
