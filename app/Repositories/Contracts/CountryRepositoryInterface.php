<?php

namespace App\Repositories\Contracts;

use App\Models\Country;

interface CountryRepositoryInterface
{
    public function findByUuid(string $uuid): ?Country;

    public function findById(int $id): ?Country;

    public function paginate(array $filters = [], int $perPage = 15);

    public function create(array $data): Country;

    public function update(Country $country, array $data): Country;

    public function delete(Country $country): bool;

    public function restore(string $uuid): ?Country;

    public function forceDelete(string $uuid): bool;

    public function withTrashed(): self;
}
