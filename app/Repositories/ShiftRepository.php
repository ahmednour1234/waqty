<?php

namespace App\Repositories;

use App\Models\Employee;
use App\Models\Provider;
use App\Models\ProviderBranch;
use App\Models\Shift;
use App\Models\ShiftTemplate;
use App\Repositories\Contracts\ShiftRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ShiftRepository implements ShiftRepositoryInterface
{
    public function findByUuid(string $uuid): ?Shift
    {
        return Shift::whereUuid($uuid)
            ->with(['provider', 'branch', 'template', 'shiftDates.employees'])
            ->first();
    }

    public function paginateProvider(int $providerId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Shift::with(['branch', 'template'])
            ->where('provider_id', $providerId);

        $this->applyCommonFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function paginateAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Shift::with(['provider', 'branch', 'template']);

        if (isset($filters['provider_uuid'])) {
            $provider = Provider::whereUuid($filters['provider_uuid'])->first();
            if ($provider) {
                $query->where('provider_id', $provider->id);
            }
        }

        if (isset($filters['branch_uuid'])) {
            $branch = ProviderBranch::whereUuid($filters['branch_uuid'])->first();
            if ($branch) {
                $query->where('branch_id', $branch->id);
            }
        }

        if (isset($filters['employee_uuid'])) {
            $employee = Employee::whereUuid($filters['employee_uuid'])->first();
            if ($employee) {
                $query->whereHas('shiftDates.employeeAssignments', fn($q) => $q->where('employee_id', $employee->id));
            }
        }

        $this->applyCommonFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Shift
    {
        return Shift::create($data);
    }

    public function update(Shift $shift, array $data): Shift
    {
        $shift->update($data);
        return $shift->fresh(['branch', 'template']);
    }

    public function softDelete(Shift $shift): bool
    {
        return (bool) $shift->delete();
    }

    private function applyCommonFilters($query, array $filters): void
    {
        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (isset($filters['shift_template_uuid'])) {
            $template = ShiftTemplate::whereUuid($filters['shift_template_uuid'])->first();
            if ($template) {
                $query->where('shift_template_id', $template->id);
            }
        }

        if (isset($filters['branch_uuid']) && !isset($filters['_branch_applied'])) {
            $branch = ProviderBranch::whereUuid($filters['branch_uuid'])->first();
            if ($branch) {
                $query->where('branch_id', $branch->id);
            }
        }

        if (isset($filters['employee_uuid']) && !isset($filters['_employee_applied'])) {
            $employee = Employee::whereUuid($filters['employee_uuid'])->first();
            if ($employee) {
                $query->whereHas('shiftDates.employeeAssignments', fn($q) => $q->where('employee_id', $employee->id));
            }
        }

        if (isset($filters['date'])) {
            $query->whereHas('shiftDates', fn($q) => $q->where('shift_date', $filters['date']));
        }
    }
}
