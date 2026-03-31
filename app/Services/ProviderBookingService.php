<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Provider;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProviderBookingService
{
    // Statuses a provider is allowed to set
    const ALLOWED_STATUSES = [
        Booking::STATUS_CONFIRMED,
        Booking::STATUS_COMPLETED,
        Booking::STATUS_CANCELLED,
        Booking::STATUS_NO_SHOW,
    ];

    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    public function index(Provider $provider, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->bookingRepository->paginateProvider($provider->id, $filters, $perPage);
    }

    public function show(Provider $provider, string $uuid): Booking
    {
        $booking = $this->bookingRepository->findByUuidForProvider($uuid, $provider->id);

        if (! $booking) {
            throw new \InvalidArgumentException(__('api.bookings.not_found'));
        }

        return $booking;
    }

    public function updateStatus(Provider $provider, string $uuid, string $status): Booking
    {
        $booking = $this->show($provider, $uuid);

        if (! in_array($status, self::ALLOWED_STATUSES)) {
            throw new \InvalidArgumentException(__('api.bookings.invalid_status'));
        }

        $extra = [];
        if ($status === Booking::STATUS_CANCELLED) {
            $extra['cancelled_at'] = now();
        }

        return $this->bookingRepository->updateStatus($booking, $status, $extra);
    }

    public function nextUpcoming(Provider $provider): ?Booking
    {
        return $this->bookingRepository->nextUpcomingForProvider($provider->id);
    }
}
