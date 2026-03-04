<?php

namespace App\Repositories\Contracts;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EmployeeRepositoryInterface
{
    public function findByEmail(string $email): ?Employee;

    public function findByUuid(string $uuid): ?Employee;

    public function paginateAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function paginateProvider(int $providerId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Employee;

    public function update(Employee $employee, array $data): Employee;

    public function softDelete(Employee $employee): bool;

    public function toggleActive(Employee $employee, bool $active): Employee;

    public function setBlocked(Employee $employee, bool $blocked): Employee;
}
