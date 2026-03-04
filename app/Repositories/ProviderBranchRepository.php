<?php

namespace App\Repositories;

use App\Models\City;
use App\Models\Country;
use App\Models\Provider;
use App\Models\ProviderBranch;
use App\Repositories\Contracts\ProviderBranchRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProviderBranchRepository implements ProviderBranchRepositoryInterface
{
    private bool $withTrashed = false;

    public function findByUuid(string $uuid): ?ProviderBranch
    {
        $query = ProviderBranch::whereUuid($uuid);

        if (!$this->withTrashed) {
            $query->whereNull('deleted_at');
        }

        return $query->first();
    }

    public function paginateAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ProviderBranch::with(['provider', 'country', 'city', 'provider.category']);

        if (isset($filters['trashed']) && $filters['trashed'] === 'only') {
            $query->onlyTrashed();
        } elseif (isset($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        } else {
            $query->whereNull('deleted_at');
        }

        if (isset($filters['provider_uuid'])) {
            $provider = Provider::whereUuid($filters['provider_uuid'])->first();
            if ($provider) {
                $query->where('provider_id', $provider->id);
            }
        }

        if (isset($filters['country_uuid'])) {
            $country = Country::whereUuid($filters['country_uuid'])->first();
            if ($country) {
                $query->where('country_id', $country->id);
            }
        } elseif (isset($filters['country_id'])) {
            $query->where('country_id', $filters['country_id']);
        }

        if (isset($filters['city_uuid'])) {
            $city = City::whereUuid($filters['city_uuid'])->first();
            if ($city) {
                $query->where('city_id', $city->id);
            }
        } elseif (isset($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }

        if (isset($filters['category_uuid'])) {
            $query->whereHas('provider', function ($q) use ($filters) {
                $category = \App\Models\Category::whereUuid($filters['category_uuid'])->first();
                if ($category) {
                    $q->where('category_id', $category->id);
                }
            });
        } elseif (isset($filters['category_id'])) {
            $query->whereHas('provider', function ($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
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

        if (isset($filters['is_main'])) {
            $query->where('is_main', $filters['is_main']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhereHas('provider', function ($providerQuery) use ($search) {
                      $providerQuery->where('name', 'LIKE', "%{$search}%")
                                    ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function paginateProvider(int $providerId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ProviderBranch::where('provider_id', $providerId)
            ->with(['country', 'city']);

        $query->whereNull('deleted_at');

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (isset($filters['is_main'])) {
            $query->where('is_main', $filters['is_main']);
        }

        if (isset($filters['city_uuid'])) {
            $city = City::whereUuid($filters['city_uuid'])->first();
            if ($city) {
                $query->where('city_id', $city->id);
            }
        } elseif (isset($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }

        if (isset($filters['country_uuid'])) {
            $country = Country::whereUuid($filters['country_uuid'])->first();
            if ($country) {
                $query->where('country_id', $country->id);
            }
        } elseif (isset($filters['country_id'])) {
            $query->where('country_id', $filters['country_id']);
        }

        return $query->orderBy('is_main', 'desc')->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function listPublic(array $filters = []): LengthAwarePaginator
    {
        $query = ProviderBranch::select([
            'id', 'uuid', 'name', 'country_id', 'city_id',
            'latitude', 'longitude', 'logo_path', 'is_main', 'created_at'
        ])
            ->where('active', true)
            ->where('blocked', false)
            ->where('banned', false)
            ->whereNull('deleted_at')
            ->with(['country', 'city']);

        if (isset($filters['provider_uuid'])) {
            $provider = Provider::whereUuid($filters['provider_uuid'])->first();
            if ($provider) {
                $query->where('provider_id', $provider->id);
            }
        }

        if (isset($filters['country_uuid'])) {
            $country = Country::whereUuid($filters['country_uuid'])->first();
            if ($country) {
                $query->where('country_id', $country->id);
            }
        } elseif (isset($filters['country_id'])) {
            $query->where('country_id', $filters['country_id']);
        }

        if (isset($filters['city_uuid'])) {
            $city = City::whereUuid($filters['city_uuid'])->first();
            if ($city) {
                $query->where('city_id', $city->id);
            }
        } elseif (isset($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }

        if (isset($filters['category_uuid'])) {
            $query->whereHas('provider', function ($q) use ($filters) {
                $category = \App\Models\Category::whereUuid($filters['category_uuid'])->first();
                if ($category) {
                    $q->where('category_id', $category->id)
                      ->where('active', true)
                      ->where('blocked', false)
                      ->where('banned', false)
                      ->whereNull('deleted_at');
                }
            });
        }

        return $query->orderBy('is_main', 'desc')->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): ProviderBranch
    {
        return ProviderBranch::create($data);
    }

    public function update(ProviderBranch $branch, array $data): ProviderBranch
    {
        $branch->update($data);
        return $branch->fresh();
    }

    public function softDelete(ProviderBranch $branch): bool
    {
        return $branch->delete();
    }

    public function restore(string $uuid): ?ProviderBranch
    {
        $branch = ProviderBranch::onlyTrashed()->whereUuid($uuid)->first();
        if ($branch) {
            $branch->restore();
            return $branch->fresh();
        }
        return null;
    }

    public function setStatus(ProviderBranch $branch, array $status): ProviderBranch
    {
        $branch->update($status);
        return $branch->fresh();
    }

    public function withTrashed(): self
    {
        $this->withTrashed = true;
        return $this;
    }
}
