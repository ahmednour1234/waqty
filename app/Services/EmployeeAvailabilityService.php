<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\ProviderBranch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeAvailabilityService
{
    // ─── Employee self-management ─────────────────────────────────────────────

    /**
     * Set employee availability manually (available / break / off).
     * Employees cannot set in_session directly — that's driven by booking sessions.
     */
    public function setStatus(Employee $employee, string $status): Employee
    {
        if (! in_array($status, Employee::MANUAL_AVAILABILITY_STATUSES)) {
            throw new \InvalidArgumentException('Invalid availability status. Allowed: ' . implode(', ', Employee::MANUAL_AVAILABILITY_STATUSES));
        }

        $employee->update([
            'availability_status'     => $status,
            'availability_updated_at' => now(),
        ]);

        return $employee->fresh();
    }

    /**
     * Start a session for a booking: marks the booking's session_started_at,
     * sets employee status to in_session.
     */
    public function startSession(Employee $employee, string $bookingUuid): Booking
    {
        return DB::transaction(function () use ($employee, $bookingUuid) {
            $booking = Booking::where('uuid', $bookingUuid)
                ->where('employee_id', $employee->id)
                ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_PENDING])
                ->whereNull('session_started_at')
                ->first();

            if (! $booking) {
                throw new \InvalidArgumentException('Booking not found or cannot start a session for it.');
            }

            $booking->update(['session_started_at' => now()]);

            $employee->update([
                'availability_status'     => Employee::AVAILABILITY_IN_SESSION,
                'availability_updated_at' => now(),
            ]);

            return $booking->fresh();
        });
    }

    /**
     * End a session for a booking: marks session_ended_at, reverts employee to available.
     */
    public function endSession(Employee $employee, string $bookingUuid): Booking
    {
        return DB::transaction(function () use ($employee, $bookingUuid) {
            $booking = Booking::where('uuid', $bookingUuid)
                ->where('employee_id', $employee->id)
                ->whereNotNull('session_started_at')
                ->whereNull('session_ended_at')
                ->first();

            if (! $booking) {
                throw new \InvalidArgumentException('Booking session not found or already ended.');
            }

            $booking->update(['session_ended_at' => now()]);

            $employee->update([
                'availability_status'     => Employee::AVAILABILITY_AVAILABLE,
                'availability_updated_at' => now(),
            ]);

            return $booking->fresh();
        });
    }

    // ─── Read by branch ──────────────────────────────────────────────────────

    /**
     * Get availability status for all employees in a branch.
     * Optionally filter by a single employee UUID.
     */
    public function getBranchAvailability(ProviderBranch $branch, ?string $employeeUuid = null): array
    {
        $query = Employee::where('branch_id', $branch->id)
            ->where('active', true)
            ->where('blocked', false);

        if ($employeeUuid) {
            $query->where('uuid', $employeeUuid);
        }

        return $query->orderBy('name')
            ->get()
            ->map(fn(Employee $e) => $this->formatEmployee($e))
            ->values()
            ->toArray();
    }

    // ─── Read by provider ────────────────────────────────────────────────────

    /**
     * Get availability for all employees across all branches (or a specific branch/employee).
     */
    public function getProviderAvailability(
        int $providerId,
        ?string $branchUuid = null,
        ?string $employeeUuid = null
    ): array {
        $query = Employee::where('provider_id', $providerId)
            ->where('active', true)
            ->where('blocked', false)
            ->with('branch:id,uuid,name');

        if ($branchUuid) {
            $query->whereHas('branch', fn($q) => $q->where('uuid', $branchUuid));
        }

        if ($employeeUuid) {
            $query->where('uuid', $employeeUuid);
        }

        return $query->orderBy('name')
            ->get()
            ->map(fn(Employee $e) => $this->formatEmployee($e, withBranch: true))
            ->values()
            ->toArray();
    }

    // ─── Format helper ───────────────────────────────────────────────────────

    private function formatEmployee(Employee $e, bool $withBranch = false): array
    {
        $data = [
            'uuid'                    => $e->uuid,
            'name'                    => $e->name,
            'job_title'               => $e->job_title,
            'availability_status'     => $e->availability_status,
            'availability_updated_at' => $e->availability_updated_at?->toIso8601String(),
        ];

        if ($withBranch && $e->relationLoaded('branch') && $e->branch) {
            $data['branch'] = [
                'uuid' => $e->branch->uuid,
                'name' => $e->branch->name,
            ];
        }

        return $data;
    }
}
