<?php

namespace App\Repositories\Contracts;

use App\Models\Subcategory;

interface SubcategoryRepositoryInterface
{
    public function findByUuid(string $uuid): ?Subcategory;

    public function findById(int $id): ?Subcategory;

    public function findByCategoryUuid(string $categoryUuid): \Illuminate\Database\Eloquent\Collection;

    public function findByCategoryId(int $categoryId): \Illuminate\Database\Eloquent\Collection;

    public function paginate(array $filters = [], int $perPage = 15);

    public function create(array $data): Subcategory;

    public function update(Subcategory $subcategory, array $data): Subcategory;

    public function delete(Subcategory $subcategory): bool;

    public function restore(string $uuid): ?Subcategory;

    public function forceDelete(string $uuid): bool;

    public function withTrashed(): self;
}
