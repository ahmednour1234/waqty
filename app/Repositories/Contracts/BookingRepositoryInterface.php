<?php

namespace App\Repositories\Contracts;

use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BookingRepositoryInterface
{
    public function findByUuid(string $uuid): ?Booking;

    public function findByUuidForUser(string $uuid, int $userId): ?Booking;

    public function findByUuidForProvider(string $uuid, int $providerId): ?Booking;

    public function findByUuidForEmployee(string $uuid, int $employeeId): ?Booking;

    public function paginateAdmin(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function paginateProvider(int $providerId, array $filters, int $perPage = 15): LengthAwarePaginator;

    public function paginateEmployee(int $employeeId, array $filters, int $perPage = 15): LengthAwarePaginator;

    public function paginateUser(int $userId, array $filters, int $perPage = 15): LengthAwarePaginator;

    public function hasConflict(int $employeeId, string $date, string $startTime, string $endTime, ?int $excludeBookingId = null): bool;

    public function create(array $data): Booking;

    public function updateStatus(Booking $booking, string $status, array $extra = []): Booking;

    public function softDelete(Booking $booking): bool;
}
