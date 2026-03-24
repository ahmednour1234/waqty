<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ShiftDate;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class EmployeeAttendanceService
{
    public function __construct(
        private AttendanceRepositoryInterface $attendanceRepository
    ) {}

    /**
     * Record a check-in for the authenticated employee.
     *
     * Validation rules (shift-level):
     *  - If shift_date_uuid is given, the employee must be assigned to that ShiftDate.
     *  - If shift_date_uuid is given, the employee must not have already checked in for it.
     *  - The employee must not currently have an open check-in (no check_out_at).
     */
    public function checkIn(array $data): Attendance
    {
        $employee = Auth::guard('employee')->user();

        // Prevent double open check-in
        $existing = $this->attendanceRepository->findActiveCheckIn($employee->id);
        if ($existing) {
            throw new \InvalidArgumentException(__('api.attendance.already_checked_in'));
        }

        $shiftDateId = null;

        if (!empty($data['shift_date_uuid'])) {
            $shiftDate = ShiftDate::where('uuid', $data['shift_date_uuid'])->first();

            if (!$shiftDate) {
                throw new ModelNotFoundException(__('api.attendance.shift_date_not_found'));
            }

            // Validate the employee is assigned to this shift date
            $isAssigned = $shiftDate->employeeAssignments()
                ->where('employee_id', $employee->id)
                ->exists();

            if (!$isAssigned) {
                throw new \InvalidArgumentException(__('api.attendance.shift_date_not_assigned'));
            }

            // Prevent duplicate check-in for the same shift date
            if ($this->attendanceRepository->hasCheckedInForShiftDate($employee->id, $shiftDate->id)) {
                throw new \InvalidArgumentException(__('api.attendance.already_checked_in_for_shift'));
            }

            $shiftDateId = $shiftDate->id;
        }

        $attendance = $this->attendanceRepository->create([
            'employee_id'   => $employee->id,
            'shift_date_id' => $shiftDateId,
            'check_in_at'   => now(),
            'notes'         => $data['notes'] ?? null,
        ]);

        return $attendance->load(['shiftDate:id,uuid,shift_date,start_time,end_time']);
    }

    /**
     * Record a check-out for the authenticated employee.
     * Computes working_minutes from check_in_at to now.
     */
    public function checkOut(array $data): Attendance
    {
        $employee = Auth::guard('employee')->user();

        $attendance = $this->attendanceRepository->findActiveCheckIn($employee->id);

        if (!$attendance) {
            throw new ModelNotFoundException(__('api.attendance.no_active_check_in'));
        }

        $checkOutAt      = now();
        $workingMinutes  = (int) $attendance->check_in_at->diffInMinutes($checkOutAt);

        return $this->attendanceRepository->update($attendance, [
            'check_out_at'    => $checkOutAt,
            'working_minutes' => $workingMinutes,
            'notes'           => $data['notes'] ?? $attendance->notes,
        ]);
    }

    /**
     * List the authenticated employee's attendance records with optional filters.
     *
     * Supported filters: date_from (Y-m-d), date_to (Y-m-d), shift_date_uuid.
     */
    public function index(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $employee = Auth::guard('employee')->user();
        return $this->attendanceRepository->paginateEmployee($employee->id, $filters, $perPage);
    }
}
