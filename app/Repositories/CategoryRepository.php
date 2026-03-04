<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CategoryRepository implements CategoryRepositoryInterface
{
    private bool $withTrashed = false;

    public function findByUuid(string $uuid): ?Category
    {
        $query = Category::whereUuid($uuid);
        
        if (!$this->withTrashed) {
            $query->whereNull('deleted_at');
        }

        return $query->first();
    }

    public function findById(int $id): ?Category
    {
        $query = Category::where('id', $id);
        
        if (!$this->withTrashed) {
            $query->whereNull('deleted_at');
        }

        return $query->first();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Category::query();

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

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        return $category->fresh();
    }

    public function delete(Category $category): bool
    {
        return $category->delete();
    }

    public function restore(string $uuid): ?Category
    {
        $category = Category::onlyTrashed()->whereUuid($uuid)->first();
        if ($category) {
            $category->restore();
            return $category->fresh();
        }
        return null;
    }

    public function forceDelete(string $uuid): bool
    {
        $category = Category::withTrashed()->whereUuid($uuid)->first();
        if ($category) {
            return $category->forceDelete();
        }
        return false;
    }

    public function withTrashed(): self
    {
        $this->withTrashed = true;
        return $this;
    }
}
