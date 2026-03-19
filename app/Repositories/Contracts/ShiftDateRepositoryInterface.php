<?php

namespace App\Repositories\Contracts;

use App\Models\ShiftDate;
use App\Models\ShiftDateEmployee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ShiftDateRepositoryInterface
{
    public function findByUuid(string $uuid): ?ShiftDate;
    public function create(array $data): ShiftDate;
    public function paginateEmployee(int $employeeId, array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function assignEmployee(ShiftDate $shiftDate, int $employeeId): ShiftDateEmployee;
    public function isEmployeeAssigned(int $shiftDateId, int $employeeId): bool;
    public function getEmployeeConflicts(int $employeeId, string $date, string $startTime, string $endTime, ?int $excludeShiftDateId = null): Collection;
}
