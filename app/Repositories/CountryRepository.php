<?php

namespace App\Repositories;

use App\Models\Country;
use App\Repositories\Contracts\CountryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CountryRepository implements CountryRepositoryInterface
{
    private bool $withTrashed = false;

    public function findByUuid(string $uuid): ?Country
    {
        $query = Country::whereUuid($uuid);
        
        if (!$this->withTrashed) {
            $query->whereNull('deleted_at');
        }

        return $query->first();
    }

    public function findById(int $id): ?Country
    {
        $query = Country::where('id', $id);
        
        if (!$this->withTrashed) {
            $query->whereNull('deleted_at');
        }

        return $query->first();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Country::query();

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
                $q->whereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"]);
            });
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        return $query->orderBy('sort_order')->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Country
    {
        return Country::create($data);
    }

    public function update(Country $country, array $data): Country
    {
        $country->update($data);
        return $country->fresh();
    }

    public function delete(Country $country): bool
    {
        return $country->delete();
    }

    public function restore(string $uuid): ?Country
    {
        $country = Country::onlyTrashed()->whereUuid($uuid)->first();
        if ($country) {
            $country->restore();
            return $country->fresh();
        }
        return null;
    }

    public function forceDelete(string $uuid): bool
    {
        $country = Country::withTrashed()->whereUuid($uuid)->first();
        if ($country) {
            return $country->forceDelete();
        }
        return false;
    }

    public function withTrashed(): self
    {
        $this->withTrashed = true;
        return $this;
    }
}
