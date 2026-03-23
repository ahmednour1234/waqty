<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserBookingService
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    public function index(User $user, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->bookingRepository->paginateUser($user->id, $filters, $perPage);
    }

    public function show(User $user, string $uuid): Booking
    {
        $booking = $this->bookingRepository->findByUuidForUser($uuid, $user->id);

        if (! $booking) {
            throw new \InvalidArgumentException(__('api.bookings.not_found'));
        }

        return $booking;
    }

    public function cancel(User $user, string $uuid, ?string $reason): Booking
    {
        $booking = $this->show($user, $uuid);

        if (! $booking->can_cancel) {
            throw new \InvalidArgumentException(__('api.bookings.cannot_cancel'));
        }

        return $this->bookingRepository->updateStatus($booking, Booking::STATUS_CANCELLED, [
            'cancellation_reason' => $reason,
            'cancelled_at'        => now(),
        ]);
    }
}
