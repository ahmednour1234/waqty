<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Subcategory;
use App\Repositories\Contracts\SubcategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubcategoryRepository implements SubcategoryRepositoryInterface
{
    private bool $withTrashed = false;

    public function findByUuid(string $uuid): ?Subcategory
    {
        $query = Subcategory::whereUuid($uuid);
        
        if (!$this->withTrashed) {
            $query->whereNull('deleted_at');
        }

        return $query->first();
    }

    public function findById(int $id): ?Subcategory
    {
        $query = Subcategory::where('id', $id);
        
        if (!$this->withTrashed) {
            $query->whereNull('deleted_at');
        }

        return $query->first();
    }

    public function findByCategoryUuid(string $categoryUuid): \Illuminate\Database\Eloquent\Collection
    {
        $category = Category::whereUuid($categoryUuid)->first();
        if (!$category) {
            return collect();
        }

        $query = Subcategory::where('category_id', $category->id)
            ->where('active', true)
            ->whereNull('deleted_at')
            ->orderBy('sort_order');

        return $query->get();
    }

    public function findByCategoryId(int $categoryId): \Illuminate\Database\Eloquent\Collection
    {
        $query = Subcategory::where('category_id', $categoryId)
            ->where('active', true)
            ->whereNull('deleted_at')
            ->orderBy('sort_order');

        return $query->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Subcategory::query();

        if (isset($filters['trashed']) && $filters['trashed'] === 'only') {
            $query->onlyTrashed();
        } elseif (isset($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        } else {
            $query->whereNull('deleted_at');
        }

        if (isset($filters['category_uuid'])) {
            $category = Category::whereUuid($filters['category_uuid'])->first();
            if ($category) {
                $query->where('category_id', $category->id);
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

    public function create(array $data): Subcategory
    {
        return Subcategory::create($data);
    }

    public function update(Subcategory $subcategory, array $data): Subcategory
    {
        $subcategory->update($data);
        return $subcategory->fresh();
    }

    public function delete(Subcategory $subcategory): bool
    {
        return $subcategory->delete();
    }

    public function restore(string $uuid): ?Subcategory
    {
        $subcategory = Subcategory::onlyTrashed()->whereUuid($uuid)->first();
        if ($subcategory) {
            $subcategory->restore();
            return $subcategory->fresh();
        }
        return null;
    }

    public function forceDelete(string $uuid): bool
    {
        $subcategory = Subcategory::withTrashed()->whereUuid($uuid)->first();
        if ($subcategory) {
            return $subcategory->forceDelete();
        }
        return false;
    }

    public function withTrashed(): self
    {
        $this->withTrashed = true;
        return $this;
    }
}
