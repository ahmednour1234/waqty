<?php

namespace App\Services;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminBookingService
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    public function index(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->bookingRepository->paginateAdmin($filters, $perPage);
    }

    public function show(string $uuid): Booking
    {
        $booking = $this->bookingRepository->findByUuid($uuid);

        if (! $booking) {
            throw new \InvalidArgumentException(__('api.bookings.not_found'));
        }

        return $booking;
    }

    public function updateStatus(string $uuid, string $status): Booking
    {
        $booking = $this->show($uuid);

        if (! in_array($status, Booking::ALL_STATUSES)) {
            throw new \InvalidArgumentException(__('api.bookings.invalid_status'));
        }

        $extra = [];
        if ($status === Booking::STATUS_CANCELLED) {
            $extra['cancelled_at'] = now();
        }

        return $this->bookingRepository->updateStatus($booking, $status, $extra);
    }

    public function destroy(string $uuid): void
    {
        $booking = $this->show($uuid);
        $this->bookingRepository->softDelete($booking);
    }
}
