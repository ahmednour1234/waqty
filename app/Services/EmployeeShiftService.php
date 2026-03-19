<?php

namespace App\Services;

use App\Models\ShiftDate;
use App\Repositories\Contracts\ShiftDateRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class EmployeeShiftService
{
    public function __construct(
        private ShiftDateRepositoryInterface $shiftDateRepository
    ) {}

    public function index(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $employee = Auth::guard('employee')->user();
        return $this->shiftDateRepository->paginateEmployee($employee->id, $filters, $perPage);
    }

    public function show(string $uuid): ShiftDate
    {
        $employee  = Auth::guard('employee')->user();
        $shiftDate = $this->shiftDateRepository->findByUuid($uuid);

        if (!$shiftDate) {
            throw new ModelNotFoundException('Shift not found');
        }

        // Ensure employee is actually assigned to this shift date
        $isAssigned = $shiftDate->employeeAssignments()
            ->where('employee_id', $employee->id)
            ->exists();

        if (!$isAssigned) {
            throw new ModelNotFoundException('Shift not found');
        }

        return $shiftDate;
    }
}
