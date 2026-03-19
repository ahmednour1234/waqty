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

    public function toggleActive(Service $service, bool $active): Service;
}
