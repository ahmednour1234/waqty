<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ProviderBranch;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BranchBookingService
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    public function index(ProviderBranch $branch, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->bookingRepository->paginateBranch($branch->id, $filters, $perPage);
    }

    public function show(ProviderBranch $branch, string $uuid): Booking
    {
        $booking = $this->bookingRepository->findByUuidForBranch($uuid, $branch->id);

        if (! $booking) {
            throw new \InvalidArgumentException(__('api.bookings.not_found'));
        }

        return $booking;
    }

    public function nextUpcoming(ProviderBranch $branch): ?Booking
    {
        return $this->bookingRepository->nextUpcomingForBranch($branch->id);
    }
}
