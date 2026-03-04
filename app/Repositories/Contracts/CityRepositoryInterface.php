<?php

namespace App\Repositories\Contracts;

use App\Models\City;

interface CityRepositoryInterface
{
    public function findByUuid(string $uuid): ?City;

    public function findById(int $id): ?City;

    public function paginate(array $filters = [], int $perPage = 15);

    public function create(array $data): City;

    public function update(City $city, array $data): City;

    public function delete(City $city): bool;

    public function restore(string $uuid): ?City;

    public function forceDelete(string $uuid): bool;

    public function withTrashed(): self;
}
