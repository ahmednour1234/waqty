<?php

namespace App\Repositories;

use App\Models\Provider;
use App\Repositories\Contracts\ProviderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProviderRepository implements ProviderRepositoryInterface
{
    private bool $withTrashed = false;

    public function findByEmail(string $email): ?Provider
    {
        $query = Provider::where('email', $email);

        if (!$this->withTrashed) {
            $query->whereNull('deleted_at');
        }

        return $query->first();
    }

    public function findByUuid(string $uuid): ?Provider
    {
        $query = Provider::whereUuid($uuid);

        if (!$this->withTrashed) {
            $query->whereNull('deleted_at');
        }

        return $query->first();
    }

    public function paginateAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Provider::with(['category', 'country', 'city']);

        if (isset($filters['trashed']) && $filters['trashed'] === 'only') {
            $query->onlyTrashed();
        } elseif (isset($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        } else {
            $query->whereNull('deleted_at');
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (isset($filters['blocked'])) {
            $query->where('blocked', $filters['blocked']);
        }

        if (isset($filters['banned'])) {
            $query->where('banned', $filters['banned']);
        }

        if (isset($filters['country_id'])) {
            $query->where('country_id', $filters['country_id']);
        }

        if (isset($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Provider
    {
        return Provider::create($data);
    }

    public function update(Provider $provider, array $data): Provider
    {
        $provider->update($data);
        return $provider->fresh();
    }

    public function softDelete(Provider $provider): bool
    {
        return $provider->delete();
    }

    public function toggleActive(Provider $provider, bool $active): Provider
    {
        $provider->update(['active' => $active]);
        return $provider->fresh();
    }

    public function setBlocked(Provider $provider, bool $blocked): Provider
    {
        $provider->update(['blocked' => $blocked]);
        return $provider->fresh();
    }

    public function setBanned(Provider $provider, bool $banned): Provider
    {
        $provider->update(['banned' => $banned]);
        return $provider->fresh();
    }

    public function restore(string $uuid): ?Provider
    {
        $provider = Provider::onlyTrashed()->whereUuid($uuid)->first();
        if ($provider) {
            $provider->restore();
            return $provider->fresh();
        }
        return null;
    }

    public function forceDelete(string $uuid): bool
    {
        $provider = Provider::withTrashed()->whereUuid($uuid)->first();
        if ($provider) {
            return $provider->forceDelete();
        }
        return false;
    }

    public function withTrashed(): self
    {
        $this->withTrashed = true;
        return $this;
    }
}
