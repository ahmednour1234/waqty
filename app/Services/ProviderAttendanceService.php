<?php

namespace App\Services;

use App\Repositories\Contracts\AttendanceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ProviderAttendanceService
{
    public function __construct(
        private AttendanceRepositoryInterface $attendanceRepository
    ) {}

    /**
     * List attendance records for all employees belonging to the authenticated provider.
     *
     * Supported filters: employee_uuid, date_from (Y-m-d), date_to (Y-m-d).
     */
    public function index(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $provider = Auth::guard('provider')->user();
        return $this->attendanceRepository->paginateProvider($provider->id, $filters, $perPage);
    }
}
