<?php

namespace App\Repositories\Contracts;

use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ServiceRepositoryInterface
{
    public function findByUuid(string $uuid): ?Service;

    public function paginateAdmin(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function paginateProvider(int $providerId, array $filters, int $perPage = 15): LengthAwarePaginator;

    public function paginateEmployee(int $providerId, array $filters, int $perPage = 15): LengthAwarePaginator;

    public function paginatePublic(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Service;

    public function update(Service $service, array $data): Service;

    public function softDelete(Service $service): bool;

    public function restore(string $uuid): ?Service;

    public function attachProvider(Service $service, int $providerId): void;

    public function softDeletePivot(Service $service, int $providerId): void;

    public function togglePivotActive(Service $service, int $providerId, bool $active): Service;

    public function isAttachedToProvider(Service $service, int $providerId): bool;

    public function paginatePublicNewest(array $filters, int $perPage): LengthAwarePaginator;

    public function paginatePublicNearest(float $lat, float $lng, float $radiusKm, array $filters, int $perPage): LengthAwarePaginator;
}
