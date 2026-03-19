<?php

namespace App\Repositories\Contracts;

use App\Models\Shift;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ShiftRepositoryInterface
{
    public function findByUuid(string $uuid): ?Shift;
    public function paginateProvider(int $providerId, array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function paginateAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function create(array $data): Shift;
    public function update(Shift $shift, array $data): Shift;
    public function softDelete(Shift $shift): bool;
}
