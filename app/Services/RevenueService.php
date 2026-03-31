<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\ProviderBranch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RevenueService
{
    // ─── Employee ────────────────────────────────────────────────────────────

    public function getEmployeeRevenue(
        Employee $employee,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = Booking::where('employee_id', $employee->id)
            ->where('status', Booking::STATUS_COMPLETED);

        $this->applyDateFilters($query, $startDate, $endDate);

        $result = $query->selectRaw('COUNT(*) as completed_bookings, COALESCE(SUM(price), 0) as total_revenue')
            ->first();

        return [
            'total_revenue'      => (float) $result->total_revenue,
            'completed_bookings' => (int) $result->completed_bookings,
        ];
    }

    // ─── Branch ──────────────────────────────────────────────────────────────

    public function getBranchRevenue(
        ProviderBranch $branch,
        ?string $employeeUuid = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        // Resolve employee filter
        $employeeId = null;
        if ($employeeUuid) {
            $emp = Employee::whereUuid($employeeUuid)->where('branch_id', $branch->id)->first();
            $employeeId = $emp?->id ?? -1;
        }

        // Total for the branch (or single employee)
        $totalsQuery = Booking::where('branch_id', $branch->id)
            ->where('status', Booking::STATUS_COMPLETED);
        if ($employeeId !== null) {
            $totalsQuery->where('employee_id', $employeeId);
        }
        $this->applyDateFilters($totalsQuery, $startDate, $endDate);
        $totals = (clone $totalsQuery)
            ->selectRaw('COUNT(*) as completed_bookings, COALESCE(SUM(price), 0) as total_revenue')
            ->first();

        // Per-employee breakdown
        $employeeQuery = Employee::where('branch_id', $branch->id);
        if ($employeeId !== null) {
            $employeeQuery->where('id', $employeeId);
        }
        $employees = $employeeQuery->get();

        $employeeStats = $employees->map(function (Employee $emp) use ($branch, $startDate, $endDate) {
            $q = Booking::where('employee_id', $emp->id)
                ->where('branch_id', $branch->id)
                ->where('status', Booking::STATUS_COMPLETED);
            $this->applyDateFilters($q, $startDate, $endDate);
            $stat = $q->selectRaw('COUNT(*) as completed_bookings, COALESCE(SUM(price), 0) as total_revenue')
                ->first();

            return [
                'uuid'               => $emp->uuid,
                'name'               => $emp->name,
                'job_title'          => $emp->job_title,
                'total_revenue'      => (float) $stat->total_revenue,
                'completed_bookings' => (int) $stat->completed_bookings,
            ];
        })->values()->toArray();

        return [
            'total_revenue'      => (float) $totals->total_revenue,
            'completed_bookings' => (int) $totals->completed_bookings,
            'employees'          => $employeeStats,
        ];
    }

    // ─── Provider ────────────────────────────────────────────────────────────

    public function getProviderRevenue(
        int $providerId,
        ?string $branchUuid = null,
        ?string $employeeUuid = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        // Resolve branch filter
        $branchId = null;
        if ($branchUuid) {
            $branch = ProviderBranch::whereUuid($branchUuid)->where('provider_id', $providerId)->first();
            $branchId = $branch?->id ?? -1;
        }

        // Resolve employee filter
        $employeeId = null;
        if ($employeeUuid) {
            $empQuery = Employee::whereUuid($employeeUuid)->where('provider_id', $providerId);
            if ($branchId !== null && $branchId !== -1) {
                $empQuery->where('branch_id', $branchId);
            }
            $emp = $empQuery->first();
            $employeeId = $emp?->id ?? -1;
        }

        // Provider-level totals
        $totalsQuery = Booking::where('provider_id', $providerId)
            ->where('status', Booking::STATUS_COMPLETED);
        if ($branchId !== null) {
            $totalsQuery->where('branch_id', $branchId);
        }
        if ($employeeId !== null) {
            $totalsQuery->where('employee_id', $employeeId);
        }
        $this->applyDateFilters($totalsQuery, $startDate, $endDate);
        $totals = (clone $totalsQuery)
            ->selectRaw('COUNT(*) as completed_bookings, COALESCE(SUM(price), 0) as total_revenue')
            ->first();

        // Branches
        $branchQuery = ProviderBranch::where('provider_id', $providerId);
        if ($branchId !== null) {
            $branchQuery->where('id', $branchId);
        }
        $branches = $branchQuery->get();

        $branchStats = $branches->map(function (ProviderBranch $br) use ($providerId, $employeeId, $startDate, $endDate) {
            $brTotalsQuery = Booking::where('branch_id', $br->id)
                ->where('provider_id', $providerId)
                ->where('status', Booking::STATUS_COMPLETED);
            if ($employeeId !== null) {
                $brTotalsQuery->where('employee_id', $employeeId);
            }
            $this->applyDateFilters($brTotalsQuery, $startDate, $endDate);
            $brTotals = (clone $brTotalsQuery)
                ->selectRaw('COUNT(*) as completed_bookings, COALESCE(SUM(price), 0) as total_revenue')
                ->first();

            // Employees per branch
            $empQuery = Employee::where('branch_id', $br->id)->where('provider_id', $providerId);
            if ($employeeId !== null) {
                $empQuery->where('id', $employeeId);
            }
            $employees = $empQuery->get();

            $employeeStats = $employees->map(function (Employee $emp) use ($br, $startDate, $endDate) {
                $q = Booking::where('employee_id', $emp->id)
                    ->where('branch_id', $br->id)
                    ->where('status', Booking::STATUS_COMPLETED);
                $this->applyDateFilters($q, $startDate, $endDate);
                $stat = $q->selectRaw('COUNT(*) as completed_bookings, COALESCE(SUM(price), 0) as total_revenue')
                    ->first();

                return [
                    'uuid'               => $emp->uuid,
                    'name'               => $emp->name,
                    'job_title'          => $emp->job_title,
                    'total_revenue'      => (float) $stat->total_revenue,
                    'completed_bookings' => (int) $stat->completed_bookings,
                ];
            })->values()->toArray();

            return [
                'uuid'               => $br->uuid,
                'name'               => $br->name,
                'total_revenue'      => (float) $brTotals->total_revenue,
                'completed_bookings' => (int) $brTotals->completed_bookings,
                'employees'          => $employeeStats,
            ];
        })->values()->toArray();

        return [
            'total_revenue'      => (float) $totals->total_revenue,
            'completed_bookings' => (int) $totals->completed_bookings,
            'branches'           => $branchStats,
        ];
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function applyDateFilters($query, ?string $startDate, ?string $endDate): void
    {
        if ($startDate) {
            $query->whereDate('booking_date', '>=', Carbon::parse($startDate)->format('Y-m-d'));
        }
        if ($endDate) {
            $query->whereDate('booking_date', '<=', Carbon::parse($endDate)->format('Y-m-d'));
        }
    }
}
