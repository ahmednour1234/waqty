<?php

namespace App\Repositories;

use App\Models\PricingGroup;
use App\Repositories\Contracts\PricingGroupRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PricingGroupRepository implements PricingGroupRepositoryInterface
{
    public function findByUuid(string $uuid): ?PricingGroup
    {
        return PricingGroup::whereUuid($uuid)
            ->with(['provider', 'employees'])
            ->first();
    }

    public function findByUuidAndProvider(string $uuid, int $providerId): ?PricingGroup
    {
        return PricingGroup::whereUuid($uuid)
            ->where('provider_id', $providerId)
            ->with(['employees'])
            ->first();
    }

    public function paginateProvider(int $providerId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = PricingGroup::with(['employees'])
            ->withCount('employees')
            ->where('provider_id', $providerId);

        if (isset($filters['active']) && $filters['active'] !== null) {
            $query->where('active', $filters['active']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function paginateAdmin(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = PricingGroup::with(['provider', 'employees'])
            ->withCount('employees');

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

        if (isset($filters['active']) && $filters['active'] !== null) {
            $query->where('active', $filters['active']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getActiveByProvider(int $providerId): Collection
    {
        return PricingGroup::where('provider_id', $providerId)
            ->where('active', true)
            ->whereNull('deleted_at')
            ->get();
    }

    public function create(array $data): PricingGroup
    {
        return PricingGroup::create($data);
    }

    public function update(PricingGroup $group, array $data): PricingGroup
    {
        $group->update($data);
        return $group->fresh(['employees']);
    }

    public function softDelete(PricingGroup $group): bool
    {
        return $group->delete();
    }

    public function toggleActive(PricingGroup $group, bool $active): PricingGroup
    {
        $group->update(['active' => $active]);
        return $group;
    }
}
