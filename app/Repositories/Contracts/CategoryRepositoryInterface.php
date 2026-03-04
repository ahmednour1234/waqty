<?php

namespace App\Repositories\Contracts;

use App\Models\Category;

interface CategoryRepositoryInterface
{
    public function findByUuid(string $uuid): ?Category;

    public function findById(int $id): ?Category;

    public function paginate(array $filters = [], int $perPage = 15);

    public function create(array $data): Category;

    public function update(Category $category, array $data): Category;

    public function delete(Category $category): bool;

    public function restore(string $uuid): ?Category;

    public function forceDelete(string $uuid): bool;

    public function withTrashed(): self;
}
