<?php

namespace App\Repositories\Contracts;

use App\Models\Provider;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProviderRepositoryInterface
{
    public function findByEmail(string $email): ?Provider;

    public function findByUuid(string $uuid): ?Provider;

    public function paginateAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Provider;

    public function update(Provider $provider, array $data): Provider;

    public function softDelete(Provider $provider): bool;

    public function toggleActive(Provider $provider, bool $active): Provider;

    public function setBlocked(Provider $provider, bool $blocked): Provider;

    public function setBanned(Provider $provider, bool $banned): Provider;

    public function restore(string $uuid): ?Provider;

    public function forceDelete(string $uuid): bool;

    public function withTrashed(): self;
}
