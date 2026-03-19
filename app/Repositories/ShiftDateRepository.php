<?php

namespace App\Repositories;

use App\Models\ShiftDate;
use App\Models\ShiftDateEmployee;
use App\Repositories\Contracts\ShiftDateRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ShiftDateRepository implements ShiftDateRepositoryInterface
{
    public function findByUuid(string $uuid): ?ShiftDate
    {
        return ShiftDate::whereUuid($uuid)
            ->with(['shift.provider', 'shift.branch', 'employees'])
            ->whereNull('deleted_at')
            ->first();
    }

    public function create(array $data): ShiftDate
    {
        return ShiftDate::create($data);
    }

    public function paginateEmployee(int $employeeId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ShiftDate::whereHas('employeeAssignments', fn($q) => $q->where('employee_id', $employeeId))
            ->with(['shift.provider', 'shift.branch'])
            ->whereNull('shift_dates.deleted_at');

        if (isset($filters['date'])) {
            $query->where('shift_date', $filters['date']);
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        return $query->orderBy('shift_date', 'asc')->paginate($perPage);
    }

    public function assignEmployee(ShiftDate $shiftDate, int $employeeId): ShiftDateEmployee
    {
        return ShiftDateEmployee::firstOrCreate(
            ['shift_date_id' => $shiftDate->id, 'employee_id' => $employeeId],
            ['assigned_at' => now()]
        );
    }

    public function isEmployeeAssigned(int $shiftDateId, int $employeeId): bool
    {
        return ShiftDateEmployee::where('shift_date_id', $shiftDateId)
            ->where('employee_id', $employeeId)
            ->exists();
    }

    public function getEmployeeConflicts(int $employeeId, string $date, string $startTime, string $endTime, ?int $excludeShiftDateId = null): Collection
    {
        return ShiftDate::whereHas('employeeAssignments', fn($q) => $q->where('employee_id', $employeeId))
            ->where('shift_date', $date)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->when($excludeShiftDateId !== null, fn($q) => $q->where('id', '!=', $excludeShiftDateId))
            ->whereNull('deleted_at')
            ->get();
    }
}
