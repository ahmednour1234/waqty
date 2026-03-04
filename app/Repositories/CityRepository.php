<?php

namespace App\Repositories;

use App\Models\City;
use App\Models\Country;
use App\Repositories\Contracts\CityRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CityRepository implements CityRepositoryInterface
{
    private bool $withTrashed = false;

    public function findByUuid(string $uuid): ?City
    {
        $query = City::whereUuid($uuid);
        
        if (!$this->withTrashed) {
            $query->whereNull('deleted_at');
        }

        return $query->first();
    }

    public function findById(int $id): ?City
    {
        $query = City::where('id', $id);
        
        if (!$this->withTrashed) {
            $query->whereNull('deleted_at');
        }

        return $query->first();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = City::query();

        if (isset($filters['trashed']) && $filters['trashed'] === 'only') {
            $query->onlyTrashed();
        } elseif (isset($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        } else {
            $query->whereNull('deleted_at');
        }

        if (isset($filters['country_uuid'])) {
            $country = Country::whereUuid($filters['country_uuid'])->first();
            if ($country) {
                $query->where('country_id', $country->id);
            }
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"]);
            });
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        return $query->orderBy('sort_order')->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): City
    {
        return City::create($data);
    }

    public function update(City $city, array $data): City
    {
        $city->update($data);
        return $city->fresh();
    }

    public function delete(City $city): bool
    {
        return $city->delete();
    }

    public function restore(string $uuid): ?City
    {
        $city = City::onlyTrashed()->whereUuid($uuid)->first();
        if ($city) {
            $city->restore();
            return $city->fresh();
        }
        return null;
    }

    public function forceDelete(string $uuid): bool
    {
        $city = City::withTrashed()->whereUuid($uuid)->first();
        if ($city) {
            return $city->forceDelete();
        }
        return false;
    }

    public function withTrashed(): self
    {
        $this->withTrashed = true;
        return $this;
    }
}
