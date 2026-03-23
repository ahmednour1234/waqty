<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Employee;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmployeeBookingService
{
    // Employees can only mark completed or no_show
    const ALLOWED_STATUSES = [
        Booking::STATUS_COMPLETED,
        Booking::STATUS_NO_SHOW,
    ];

    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    public function index(Employee $employee, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->bookingRepository->paginateEmployee($employee->id, $filters, $perPage);
    }

    public function show(Employee $employee, string $uuid): Booking
    {
        $booking = $this->bookingRepository->findByUuidForEmployee($uuid, $employee->id);

        if (! $booking) {
            throw new \InvalidArgumentException(__('api.bookings.not_found'));
        }

        return $booking;
    }

    public function updateStatus(Employee $employee, string $uuid, string $status): Booking
    {
        $booking = $this->show($employee, $uuid);

        if (! in_array($status, self::ALLOWED_STATUSES)) {
            throw new \InvalidArgumentException(__('api.bookings.invalid_status'));
        }

        return $this->bookingRepository->updateStatus($booking, $status);
    }
}
