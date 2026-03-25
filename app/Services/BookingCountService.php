<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\ProviderBranch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingCountService
{
    /**
     * Get booking counts for employees by branch
     *
     * @param ProviderBranch $branch
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getEmployeeBookingCountsByBranch(
        ProviderBranch $branch,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = Employee::where('branch_id', $branch->id)
            ->with(['provider'])
            ->withCount([
                'bookings' => function ($q) use ($startDate, $endDate) {
                    $this->applyDateFilters($q, $startDate, $endDate);
                }
            ])
            ->orderByDesc('bookings_count');

        return $query->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'uuid' => $employee->uuid,
                'name' => $employee->name,
                'job_title' => $employee->job_title,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'active' => $employee->active,
                'branch_uuid' => $employee->branch->uuid ?? null,
                'booking_count' => $employee->bookings_count,
            ];
        })->toArray();
    }

    /**
     * Get booking counts for employees by provider (across all branches or specific branch)
     *
     * @param int $providerId
     * @param string|null $branchUuid
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getEmployeeBookingCountsByProvider(
        int $providerId,
        ?string $branchUuid = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = Employee::where('provider_id', $providerId);

        // Filter by specific branch if provided
        if ($branchUuid) {
            $query->whereHas('branch', function ($q) use ($branchUuid) {
                $q->where('uuid', $branchUuid);
            });
        }

        $query->with(['branch'])
            ->withCount([
                'bookings' => function ($q) use ($startDate, $endDate) {
                    $this->applyDateFilters($q, $startDate, $endDate);
                }
            ])
            ->orderByDesc('bookings_count');

        return $query->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'uuid' => $employee->uuid,
                'name' => $employee->name,
                'job_title' => $employee->job_title,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'active' => $employee->active,
                'branch_uuid' => $employee->branch->uuid ?? null,
                'branch_name' => $employee->branch->name ?? null,
                'booking_count' => $employee->bookings_count,
            ];
        })->toArray();
    }

    /**
     * Get detailed booking stats for an employee
     *
     * @param Employee $employee
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getEmployeeBookingStats(
        Employee $employee,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = $employee->bookings();

        $this->applyDateFilters($query, $startDate, $endDate);

        $bookings = $query->get();

        $statsByStatus = $bookings->groupBy('status')->map->count();

        return [
            'total' => $bookings->count(),
            'by_status' => $statsByStatus->toArray(),
            'pending' => $statsByStatus->get('pending', 0),
            'confirmed' => $statsByStatus->get('confirmed', 0),
            'completed' => $statsByStatus->get('completed', 0),
            'cancelled' => $statsByStatus->get('cancelled', 0),
            'no_show' => $statsByStatus->get('no_show', 0),
        ];
    }

    /**
     * Apply date range filters to booking query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $startDate
     * @param string|null $endDate
     * @return void
     */
    private function applyDateFilters($query, ?string $startDate = null, ?string $endDate = null): void
    {
        if ($startDate) {
            $query->whereDate('booking_date', '>=', Carbon::parse($startDate)->format('Y-m-d'));
        }

        if ($endDate) {
            $query->whereDate('booking_date', '<=', Carbon::parse($endDate)->format('Y-m-d'));
        }
    }
}
