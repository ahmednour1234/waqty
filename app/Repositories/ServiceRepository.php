<?php

namespace App\Repositories;

use App\Models\Provider;
use App\Models\Service;
use App\Models\Subcategory;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as PaginatorImpl;
use Illuminate\Support\Facades\DB;

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
        $query = Service::with(['providers', 'subCategory', 'defaultPrices'])
            ->whereHas('providers', fn ($q) => $q
                ->where('providers.active', true)
                ->where('providers.blocked', false)
                ->where('providers.banned', false)
                ->whereNull('providers.deleted_at')
                ->whereNull('provider_service.deleted_at')
                ->where('provider_service.active', true)
            );

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

    public function paginatePublicNewest(int $perPage): LengthAwarePaginator
    {
        return Service::with(['providers', 'subCategory', 'defaultPrices'])
            ->whereNull('deleted_at')
            ->whereHas('providers', fn ($q) => $q
                ->where('providers.active', true)
                ->where('providers.blocked', false)
                ->where('providers.banned', false)
                ->whereNull('providers.deleted_at')
                ->whereNull('provider_service.deleted_at')
                ->where('provider_service.active', true)
            )
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function paginatePublicNearest(float $lat, float $lng, float $radiusKm, int $perPage): LengthAwarePaginator
    {
        $latRad = deg2rad($lat);
        $lngRad = deg2rad($lng);

        $rows = DB::table('services as s')
            ->selectRaw('s.id, MIN(6371 * acos(
                cos(?) * cos(radians(pb.latitude)) * cos(radians(pb.longitude) - ?)
                + sin(?) * sin(radians(pb.latitude))
            )) as min_dist', [$latRad, $lngRad, $latRad])
            ->join('provider_service as ps', 's.id', '=', 'ps.service_id')
            ->join('providers as pv', 'ps.provider_id', '=', 'pv.id')
            ->join('provider_branches as pb', 'pb.provider_id', '=', 'pv.id')
            ->whereNull('s.deleted_at')
            ->whereNull('ps.deleted_at')->where('ps.active', true)
            ->where('pv.active', true)->where('pv.blocked', false)->where('pv.banned', false)->whereNull('pv.deleted_at')
            ->where('pb.active', true)->where('pb.blocked', false)->where('pb.banned', false)->whereNull('pb.deleted_at')
            ->whereNotNull('pb.latitude')->whereNotNull('pb.longitude')
            ->groupBy('s.id')
            ->havingRaw('min_dist <= ?', [$radiusKm])
            ->orderBy('min_dist')
            ->get();

        $total = $rows->count();

        if ($total === 0) {
            return new PaginatorImpl([], 0, $perPage, 1);
        }

        $page     = (int) request()->input('page', 1);
        $pagedIds = $rows->slice(($page - 1) * $perPage, $perPage)->pluck('id')->toArray();

        $services = Service::with(['providers', 'subCategory', 'defaultPrices'])
            ->whereIn('id', $pagedIds)
            ->get()
            ->sortBy(fn ($s) => array_search($s->id, $pagedIds))
            ->values();

        return new PaginatorImpl($services, $total, $perPage, $page, [
            'path'  => request()->url(),
            'query' => request()->query(),
        ]);
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
