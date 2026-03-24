<?php

namespace App\Repositories;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\ShiftDate;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    public function findByUuid(string $uuid): ?Attendance
    {
        return Attendance::where('uuid', $uuid)
            ->with(['employee:id,uuid,name', 'shiftDate:id,uuid,shift_date,start_time,end_time'])
            ->first();
    }

    public function findActiveCheckIn(int $employeeId): ?Attendance
    {
        return Attendance::where('employee_id', $employeeId)
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->latest('check_in_at')
            ->first();
    }

    public function hasCheckedInForShiftDate(int $employeeId, int $shiftDateId): bool
    {
        return Attendance::where('employee_id', $employeeId)
            ->where('shift_date_id', $shiftDateId)
            ->exists();
    }

    public function paginateEmployee(int $employeeId, array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Attendance::with(['shiftDate:id,uuid,shift_date,start_time,end_time'])
            ->where('employee_id', $employeeId);

        $this->applyDateFilters($query, $filters);

        if (isset($filters['shift_date_uuid'])) {
            $shiftDate = ShiftDate::where('uuid', $filters['shift_date_uuid'])->first();
            if ($shiftDate) {
                $query->where('shift_date_id', $shiftDate->id);
            }
        }

        return $query->orderBy('check_in_at', 'desc')->paginate($perPage);
    }

    public function paginateProvider(int $providerId, array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Attendance::with([
                'employee:id,uuid,name',
                'shiftDate:id,uuid,shift_date,start_time,end_time',
            ])
            ->whereHas('employee', fn($q) => $q->where('provider_id', $providerId));

        if (isset($filters['employee_uuid'])) {
            $employee = Employee::where('uuid', $filters['employee_uuid'])
                ->where('provider_id', $providerId)
                ->first();

            if ($employee) {
                $query->where('employee_id', $employee->id);
            } else {
                // UUID provided but doesn't belong to this provider — return nothing
                $query->whereRaw('1 = 0');
            }
        }

        $this->applyDateFilters($query, $filters);

        return $query->orderBy('check_in_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Attendance
    {
        return Attendance::create($data);
    }

    public function update(Attendance $attendance, array $data): Attendance
    {
        $attendance->update($data);
        return $attendance->fresh(['employee:id,uuid,name', 'shiftDate:id,uuid,shift_date,start_time,end_time']);
    }

    private function applyDateFilters($query, array $filters): void
    {
        if (isset($filters['date_from'])) {
            $query->where('check_in_at', '>=', $filters['date_from'] . ' 00:00:00');
        }

        if (isset($filters['date_to'])) {
            $query->where('check_in_at', '<=', $filters['date_to'] . ' 23:59:59');
        }
    }
}
