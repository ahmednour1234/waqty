<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingActivity;
use App\Models\Provider;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProviderBookingService
{
    // Statuses a provider is allowed to set manually
    const ALLOWED_STATUSES = [
        Booking::STATUS_CONFIRMED,
        Booking::STATUS_ARRIVED,
        Booking::STATUS_IN_SERVICE,
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

        $previousStatus = $booking->status;
        $extra = [];
        if ($status === Booking::STATUS_CANCELLED) {
            $extra['cancelled_at'] = now();
        }

        $booking = $this->bookingRepository->updateStatus($booking, $status, $extra);

        $this->logActivity($booking, BookingActivity::EVENT_STATUS_CHANGED, $previousStatus, $status, [
            'actor_type' => BookingActivity::ACTOR_PROVIDER,
            'actor_id'   => $provider->id,
            'actor_name' => $provider->name,
        ]);

        return $booking;
    }

    /**
     * Advance the booking to the next status in the linear flow.
     * pending → confirmed → arrived → in_service → completed
     */
    public function advance(Provider $provider, string $uuid): Booking
    {
        $booking = $this->show($provider, $uuid);
        $flow    = Booking::STATUS_FLOW;
        $current = array_search($booking->status, $flow, true);

        if ($current === false || $current >= count($flow) - 1) {
            throw new \InvalidArgumentException(__('api.bookings.cannot_advance'));
        }

        $nextStatus = $flow[$current + 1];

        return $this->updateStatus($provider, $uuid, $nextStatus);
    }

    /**
     * Cancel a booking with an optional reason. Only cancellable from active statuses.
     */
    public function cancel(Provider $provider, string $uuid, ?string $reason, string $actorName): Booking
    {
        $booking = $this->show($provider, $uuid);

        $nonCancellable = [Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED, Booking::STATUS_NO_SHOW];
        if (in_array($booking->status, $nonCancellable)) {
            throw new \InvalidArgumentException(__('api.bookings.cannot_cancel'));
        }

        $previousStatus = $booking->status;
        $extra = [
            'cancelled_at'        => now(),
            'cancellation_reason' => $reason,
        ];

        $booking = $this->bookingRepository->updateStatus($booking, Booking::STATUS_CANCELLED, $extra);

        $this->logActivity($booking, BookingActivity::EVENT_STATUS_CHANGED, $previousStatus, Booking::STATUS_CANCELLED, [
            'actor_type' => BookingActivity::ACTOR_PROVIDER,
            'actor_id'   => $provider->id,
            'actor_name' => $actorName,
        ]);

        return $booking;
    }

    /**
     * Log a status_changed activity on a booking.
     */
    public function logActivity(
        Booking $booking,
        string $event,
        string $from,
        string $to,
        array $actor = []
    ): BookingActivity {
        return BookingActivity::create([
            'uuid'       => Str::ulid(),
            'booking_id' => $booking->id,
            'event'      => $event,
            'description' => null,
            'actor_type' => $actor['actor_type'] ?? BookingActivity::ACTOR_SYSTEM,
            'actor_id'   => $actor['actor_id']   ?? null,
            'actor_name' => $actor['actor_name'] ?? null,
            'metadata'   => ['from' => $from, 'to' => $to],
            'created_at' => now(),
        ]);
    }

    public function nextUpcoming(Provider $provider): ?Booking
    {
        return $this->bookingRepository->nextUpcomingForProvider($provider->id);
    }
}

