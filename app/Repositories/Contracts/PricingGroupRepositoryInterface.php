<?php

namespace App\Repositories\Contracts;

use App\Models\PricingGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PricingGroupRepositoryInterface
{
    public function findByUuid(string $uuid): ?PricingGroup;

    public function findByUuidAndProvider(string $uuid, int $providerId): ?PricingGroup;

    public function paginateProvider(int $providerId, array $filters, int $perPage = 15): LengthAwarePaginator;

    public function paginateAdmin(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function getActiveByProvider(int $providerId): Collection;

    public function create(array $data): PricingGroup;

    public function update(PricingGroup $group, array $data): PricingGroup;

    public function softDelete(PricingGroup $group): bool;

    public function toggleActive(PricingGroup $group, bool $active): PricingGroup;
}
