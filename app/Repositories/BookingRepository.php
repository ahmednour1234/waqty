<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BookingRepository implements BookingRepositoryInterface
{
    public function findByUuid(string $uuid): ?Booking
    {
        return Booking::whereUuid($uuid)
            ->with(['user', 'provider', 'branch', 'employee', 'service', 'rating.user'])
            ->first();
    }

    public function findByUuidForUser(string $uuid, int $userId): ?Booking
    {
        return Booking::whereUuid($uuid)
            ->where('user_id', $userId)
            ->with(['provider', 'branch', 'employee', 'service', 'rating.user'])
            ->first();
    }

    public function findByUuidForProvider(string $uuid, int $providerId): ?Booking
    {
        return Booking::whereUuid($uuid)
            ->where('provider_id', $providerId)
            ->with(['user', 'branch', 'employee', 'service', 'rating.user'])
            ->first();
    }

    public function findByUuidForEmployee(string $uuid, int $employeeId): ?Booking
    {
        return Booking::whereUuid($uuid)
            ->where('employee_id', $employeeId)
            ->with(['user', 'provider', 'branch', 'service', 'rating.user'])
            ->first();
    }

    public function findByUuidForBranch(string $uuid, int $branchId): ?Booking
    {
        return Booking::whereUuid($uuid)
            ->where('branch_id', $branchId)
            ->with(['user', 'employee', 'service', 'rating.user'])
            ->first();
    }

    public function paginateAdmin(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Booking::with(['user', 'provider', 'branch', 'employee', 'service', 'rating.user']);

        if (!empty($filters['trashed']) && $filters['trashed'] === 'only') {
            $query->onlyTrashed();
        } elseif (!empty($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        }

        $this->applyCommonFilters($query, $filters);

        if (!empty($filters['user_uuid'])) {
            $query->whereHas('user', fn($q) => $q->where('uuid', $filters['user_uuid']));
        }

        if (!empty($filters['provider_uuid'])) {
            $query->whereHas('provider', fn($q) => $q->where('uuid', $filters['provider_uuid']));
        }

        return $query->orderBy('booking_date', 'desc')->orderBy('start_time', 'desc')->paginate($perPage);
    }

    public function paginateProvider(int $providerId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Booking::where('provider_id', $providerId)
            ->with(['user', 'branch', 'employee', 'service', 'rating.user']);

        $this->applyCommonFilters($query, $filters);

        if (!empty($filters['branch_uuid'])) {
            $query->whereHas('branch', fn($q) => $q->where('uuid', $filters['branch_uuid']));
        }

        if (!empty($filters['employee_uuid'])) {
            $query->whereHas('employee', fn($q) => $q->where('uuid', $filters['employee_uuid']));
        }

        return $query->orderBy('booking_date', 'desc')->orderBy('start_time', 'desc')->paginate($perPage);
    }

    public function paginateEmployee(int $employeeId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Booking::where('employee_id', $employeeId)
            ->with(['user', 'branch', 'service', 'rating.user']);

        $this->applyCommonFilters($query, $filters);

        if (!empty($filters['today'])) {
            $query->where('booking_date', today()->toDateString());
        }

        return $query->orderBy('booking_date', 'asc')->orderBy('start_time', 'asc')->paginate($perPage);
    }

    public function paginateBranch(int $branchId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Booking::where('branch_id', $branchId)
            ->with(['user', 'employee', 'service', 'rating.user']);

        $this->applyCommonFilters($query, $filters);

        if (!empty($filters['employee_uuid'])) {
            $query->whereHas('employee', fn($q) => $q->where('uuid', $filters['employee_uuid']));
        }

        return $query->orderBy('booking_date', 'desc')->orderBy('start_time', 'desc')->paginate($perPage);
    }

    public function paginateUser(int $userId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Booking::where('user_id', $userId)
            ->with(['provider', 'branch', 'employee', 'service', 'rating.user']);

        $this->applyCommonFilters($query, $filters);

        if (!empty($filters['upcoming'])) {
            $query->whereIn('status', \App\Models\Booking::BLOCKING_STATUSES)
                  ->where('booking_date', '>=', today()->toDateString());
        }

        if (!empty($filters['past'])) {
            $query->where(function ($q) {
                $q->whereNotIn('status', \App\Models\Booking::BLOCKING_STATUSES)
                  ->orWhere('booking_date', '<', today()->toDateString());
            });
        }

        return $query->orderBy('booking_date', 'desc')->orderBy('start_time', 'desc')->paginate($perPage);
    }

    public function hasConflict(int $employeeId, string $date, string $startTime, string $endTime, ?int $excludeBookingId = null): bool
    {
        return Booking::where('employee_id', $employeeId)
            ->where('booking_date', $date)
            ->whereIn('status', \App\Models\Booking::BLOCKING_STATUSES)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->when($excludeBookingId !== null, fn($q) => $q->where('id', '!=', $excludeBookingId))
            ->exists();
    }

    public function create(array $data): Booking
    {
        return Booking::create($data);
    }

    public function updateStatus(Booking $booking, string $status, array $extra = []): Booking
    {
        $booking->fill(array_merge(['status' => $status], $extra));
        $booking->save();
        return $booking->fresh();
    }

    public function softDelete(Booking $booking): bool
    {
        return (bool) $booking->delete();
    }

    private function applyCommonFilters($query, array $filters): void
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['booking_date'])) {
            $query->where('booking_date', $filters['booking_date']);
        }

        if (!empty($filters['from_date'])) {
            $query->where('booking_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->where('booking_date', '<=', $filters['to_date']);
        }
    }

    // ─── Next Upcoming ────────────────────────────────────────────────────────

    public function nextUpcomingForAdmin(): ?Booking
    {
        return Booking::with(['user', 'provider', 'branch', 'employee', 'service', 'latestPayment'])
            ->whereIn('status', Booking::BLOCKING_STATUSES)
            ->where(fn($q) => $q
                ->where('booking_date', '>', today()->toDateString())
                ->orWhere(fn($q2) => $q2
                    ->where('booking_date', today()->toDateString())
                    ->where('start_time', '>=', now()->format('H:i:s'))
                )
            )
            ->orderBy('booking_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->first();
    }

    public function nextUpcomingForProvider(int $providerId): ?Booking
    {
        return Booking::with(['user', 'branch', 'employee', 'service', 'latestPayment'])
            ->where('provider_id', $providerId)
            ->whereIn('status', Booking::BLOCKING_STATUSES)
            ->where(fn($q) => $q
                ->where('booking_date', '>', today()->toDateString())
                ->orWhere(fn($q2) => $q2
                    ->where('booking_date', today()->toDateString())
                    ->where('start_time', '>=', now()->format('H:i:s'))
                )
            )
            ->orderBy('booking_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->first();
    }

    public function nextUpcomingForBranch(int $branchId): ?Booking
    {
        return Booking::with(['user', 'employee', 'service', 'latestPayment'])
            ->where('branch_id', $branchId)
            ->whereIn('status', Booking::BLOCKING_STATUSES)
            ->where(fn($q) => $q
                ->where('booking_date', '>', today()->toDateString())
                ->orWhere(fn($q2) => $q2
                    ->where('booking_date', today()->toDateString())
                    ->where('start_time', '>=', now()->format('H:i:s'))
                )
            )
            ->orderBy('booking_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->first();
    }

    public function nextUpcomingForEmployee(int $employeeId): ?Booking
    {
        return Booking::with(['user', 'branch', 'service', 'latestPayment'])
            ->where('employee_id', $employeeId)
            ->whereIn('status', Booking::BLOCKING_STATUSES)
            ->where(fn($q) => $q
                ->where('booking_date', '>', today()->toDateString())
                ->orWhere(fn($q2) => $q2
                    ->where('booking_date', today()->toDateString())
                    ->where('start_time', '>=', now()->format('H:i:s'))
                )
            )
            ->orderBy('booking_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->first();
    }
}
