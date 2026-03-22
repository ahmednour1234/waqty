<?php

namespace App\Repositories\Contracts;

use App\Models\ServicePrice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ServicePriceRepositoryInterface
{
    public function findByUuid(string $uuid): ?ServicePrice;

    public function findByUuidAndProvider(string $uuid, int $providerId): ?ServicePrice;

    public function paginateProvider(int $providerId, array $filters, int $perPage = 15): LengthAwarePaginator;

    public function paginateAdmin(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function existsDefault(int $serviceId, int $providerId, ?int $excludeId = null): bool;

    public function existsBranch(int $serviceId, int $branchId, int $providerId, ?int $excludeId = null): bool;

    public function existsEmployee(int $serviceId, int $employeeId, int $providerId, ?int $excludeId = null): bool;

    public function existsGroup(int $serviceId, int $groupId, int $providerId, ?int $excludeId = null): bool;

    public function create(array $data): ServicePrice;

    public function update(ServicePrice $price, array $data): ServicePrice;

    public function softDelete(ServicePrice $price): bool;

    public function toggleActive(ServicePrice $price, bool $active): ServicePrice;
}
