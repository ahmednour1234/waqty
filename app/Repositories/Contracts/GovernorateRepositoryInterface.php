<?php

namespace App\Repositories\Contracts;

use App\Models\Governorate;

interface GovernorateRepositoryInterface
{
    public function findByUuid(string $uuid): ?Governorate;

    public function findById(int $id): ?Governorate;

    public function paginate(array $filters = [], int $perPage = 15);

    public function all(array $filters = []);

    public function create(array $data): Governorate;

    public function update(Governorate $governorate, array $data): Governorate;

    public function delete(Governorate $governorate): bool;

    public function restore(string $uuid): ?Governorate;

    public function forceDelete(string $uuid): bool;

    public function withTrashed(): self;
}
