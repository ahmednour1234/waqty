<?php

namespace App\Repositories\Contracts;

use App\Models\PricingGroupEmployee;
use Illuminate\Database\Eloquent\Collection;

interface PricingGroupEmployeeRepositoryInterface
{
    public function getByGroup(int $groupId): Collection;

    public function syncEmployees(int $groupId, array $employeeIds): void;

    public function addEmployees(int $groupId, array $employeeIds): void;

    public function removeEmployees(int $groupId, array $employeeIds): void;

    public function getGroupIdsForEmployee(int $employeeId): array;
}
