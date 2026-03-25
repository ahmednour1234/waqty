<?php

namespace App\Repositories;

use App\Models\Governorate;
use App\Repositories\Contracts\GovernorateRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class GovernorateRepository implements GovernorateRepositoryInterface
{
    private bool $withTrashed = false;

    public function findByUuid(string $uuid): ?Governorate
    {
        $query = Governorate::whereUuid($uuid);

        if (!$this->withTrashed) {
            $query->whereNull('deleted_at');
        }

        return $query->first();
    }

    public function findById(int $id): ?Governorate
    {
        $query = Governorate::where('id', $id);

        if (!$this->withTrashed) {
            $query->whereNull('deleted_at');
        }

        return $query->first();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Governorate::query();

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

    public function all(array $filters = []): Collection
    {
        $query = Governorate::whereNull('deleted_at')->where('active', true);

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"]);
            });
        }

        return $query->orderBy('sort_order')->orderBy('created_at', 'desc')->get();
    }

    public function create(array $data): Governorate
    {
        return Governorate::create($data);
    }

    public function update(Governorate $governorate, array $data): Governorate
    {
        $governorate->update($data);
        return $governorate->fresh();
    }

    public function delete(Governorate $governorate): bool
    {
        return $governorate->delete();
    }

    public function restore(string $uuid): ?Governorate
    {
        $governorate = Governorate::onlyTrashed()->whereUuid($uuid)->first();
        if ($governorate) {
            $governorate->restore();
            return $governorate->fresh();
        }
        return null;
    }

    public function forceDelete(string $uuid): bool
    {
        $governorate = Governorate::withTrashed()->whereUuid($uuid)->first();
        if ($governorate) {
            return $governorate->forceDelete();
        }
        return false;
    }

    public function withTrashed(): self
    {
        $this->withTrashed = true;
        return $this;
    }
}
