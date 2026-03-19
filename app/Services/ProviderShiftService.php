<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\ProviderBranch;
use App\Models\Shift;
use App\Repositories\Contracts\ShiftDateRepositoryInterface;
use App\Repositories\Contracts\ShiftRepositoryInterface;
use App\Repositories\Contracts\ShiftTemplateRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProviderShiftService
{
    public function __construct(
        private ShiftRepositoryInterface         $shiftRepository,
        private ShiftTemplateRepositoryInterface $templateRepository,
        private ShiftDateRepositoryInterface     $shiftDateRepository
    ) {}

    public function index(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $provider = Auth::guard('provider')->user();
        return $this->shiftRepository->paginateProvider($provider->id, $filters, $perPage);
    }

    public function store(array $data): Shift
    {
        $provider = Auth::guard('provider')->user();

        return DB::transaction(function () use ($data, $provider) {
            // Resolve template or use manual times
            $template = null;
            $times    = [];

            if (!empty($data['shift_template_uuid'])) {
                $template = $this->templateRepository->findByUuid($data['shift_template_uuid']);
                if (!$template || $template->provider_id !== $provider->id) {
                    throw new ModelNotFoundException('Shift template not found');
                }
                $times = [
                    'start_time'  => $template->start_time,
                    'end_time'    => $template->end_time,
                    'break_start' => $template->break_start,
                    'break_end'   => $template->break_end,
                ];
            } else {
                $times = [
                    'start_time'  => $data['start_time'],
                    'end_time'    => $data['end_time'],
                    'break_start' => $data['break_start'] ?? null,
                    'break_end'   => $data['break_end'] ?? null,
                ];
            }

            // Resolve branch
            $branchId = null;
            if (!empty($data['branch_uuid'])) {
                $branch = ProviderBranch::whereUuid($data['branch_uuid'])->first();
                if (!$branch || $branch->provider_id !== $provider->id) {
                    throw new ModelNotFoundException('Branch not found');
                }
                $branchId = $branch->id;
            }

            // Resolve employees (deduplicated, scoped to this provider)
            $employeeIds = [];
            foreach (array_unique($data['employee_uuids'] ?? []) as $uuid) {
                $employee = Employee::whereUuid($uuid)->first();
                if (!$employee || $employee->provider_id !== $provider->id) {
                    throw new \InvalidArgumentException(__('api.shifts.employee_not_found', ['uuid' => $uuid]));
                }
                $employeeIds[] = $employee->id;
            }

            // Generate dates
            $dates = $this->resolveDates($data);

            if (empty($dates)) {
                throw new \InvalidArgumentException(__('api.shifts.no_dates_generated'));
            }

            // Create parent Shift
            $shift = $this->shiftRepository->create([
                'provider_id'       => $provider->id,
                'branch_id'         => $branchId,
                'shift_template_id' => $template?->id,
                'title'             => $data['title'] ?? null,
                'notes'             => $data['notes'] ?? null,
                'created_by_type'   => 'provider',
                'created_by_id'     => $provider->id,
                'active'            => $data['active'] ?? true,
            ]);

            // Generate ShiftDate rows and assign employees
            foreach ($dates as $date) {
                $shiftDate = $this->shiftDateRepository->create(array_merge(
                    ['shift_id' => $shift->id, 'shift_date' => $date],
                    $times
                ));

                foreach ($employeeIds as $employeeId) {
                    $conflicts = $this->shiftDateRepository->getEmployeeConflicts(
                        $employeeId,
                        $date,
                        $times['start_time'],
                        $times['end_time']
                    );

                    if ($conflicts->isNotEmpty()) {
                        throw new \InvalidArgumentException(
                            __('api.shifts.employee_overlap', ['date' => $date])
                        );
                    }

                    $this->shiftDateRepository->assignEmployee($shiftDate, $employeeId);
                }
            }

            return $shift->load(['template', 'branch', 'shiftDates.employees']);
        });
    }

    public function show(string $uuid): Shift
    {
        $provider = Auth::guard('provider')->user();
        $shift    = $this->shiftRepository->findByUuid($uuid);

        if (!$shift || $shift->provider_id !== $provider->id) {
            throw new ModelNotFoundException('Shift not found');
        }

        return $shift;
    }

    public function update(string $uuid, array $data): Shift
    {
        $provider = Auth::guard('provider')->user();
        $shift    = $this->shiftRepository->findByUuid($uuid);

        if (!$shift || $shift->provider_id !== $provider->id) {
            throw new ModelNotFoundException('Shift not found');
        }

        return DB::transaction(function () use ($shift, $data, $provider) {
            $updateData = array_filter([
                'title'  => $data['title'] ?? null,
                'notes'  => $data['notes'] ?? null,
                'active' => $data['active'] ?? null,
            ], fn($v) => $v !== null);

            if (isset($data['active'])) {
                $updateData['active'] = $data['active'];
            }

            if (array_key_exists('branch_uuid', $data)) {
                if (empty($data['branch_uuid'])) {
                    $updateData['branch_id'] = null;
                } else {
                    $branch = ProviderBranch::whereUuid($data['branch_uuid'])->first();
                    if (!$branch || $branch->provider_id !== $provider->id) {
                        throw new ModelNotFoundException('Branch not found');
                    }
                    $updateData['branch_id'] = $branch->id;
                }
            }

            return $this->shiftRepository->update($shift, $updateData);
        });
    }

    public function destroy(string $uuid): bool
    {
        $provider = Auth::guard('provider')->user();
        $shift    = $this->shiftRepository->findByUuid($uuid);

        if (!$shift || $shift->provider_id !== $provider->id) {
            throw new ModelNotFoundException('Shift not found');
        }

        return DB::transaction(function () use ($shift) {
            // Cascade soft delete to shift_dates
            $shift->shiftDates()->whereNull('deleted_at')->each(fn($d) => $d->delete());
            return $this->shiftRepository->softDelete($shift);
        });
    }

    /**
     * Resolve dates from either an explicit dates[] or weekdays+range.
     */
    private function resolveDates(array $data): array
    {
        if (!empty($data['dates'])) {
            return array_values(array_unique($data['dates']));
        }

        $dayMap = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];

        $targetDays = array_map(
            fn($d) => $dayMap[strtolower($d)],
            $data['weekdays']
        );

        $start   = Carbon::parse($data['from_date']);
        $end     = Carbon::parse($data['to_date']);
        $dates   = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            if (in_array($current->dayOfWeek, $targetDays)) {
                $dates[] = $current->format('Y-m-d');
            }
            $current->addDay();
        }

        return $dates;
    }
}
