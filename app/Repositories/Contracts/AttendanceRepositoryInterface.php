<?php

namespace App\Repositories\Contracts;

use App\Models\Attendance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AttendanceRepositoryInterface
{
    public function findByUuid(string $uuid): ?Attendance;

    /**
     * Find the most recent open check-in (no check_out_at) for the given employee.
     */
    public function findActiveCheckIn(int $employeeId): ?Attendance;

    /**
     * Check whether the employee already has an attendance record for a specific shift date.
     */
    public function hasCheckedInForShiftDate(int $employeeId, int $shiftDateId): bool;

    public function paginateEmployee(int $employeeId, array $filters, int $perPage): LengthAwarePaginator;

    public function paginateProvider(int $providerId, array $filters, int $perPage): LengthAwarePaginator;

    public function create(array $data): Attendance;

    public function update(Attendance $attendance, array $data): Attendance;
}
