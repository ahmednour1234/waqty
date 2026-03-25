<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Provider;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BookingRatingService
{
    public function rateBooking(User $user, string $bookingUuid, array $data): Rating
    {
        $booking = Booking::whereUuid($bookingUuid)
            ->where('user_id', $user->id)
            ->first();

        if (! $booking) {
            throw new \InvalidArgumentException('Booking not found.');
        }

        if ($booking->status !== Booking::STATUS_COMPLETED) {
            throw new \InvalidArgumentException('Only completed bookings can be rated.');
        }

        $rating = Rating::withTrashed()->firstOrNew([
            'booking_id' => $booking->id,
        ]);

        if ($rating->exists && $rating->trashed()) {
            $rating->restore();
        }

        $rating->fill([
            'user_id' => $user->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'active' => $data['active'] ?? true,
        ]);

        $rating->save();

        return $rating->fresh(['booking', 'user']);
    }

    public function employeeRatings(Employee $employee, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Rating::query()
            ->with(['booking', 'user'])
            ->whereHas('booking', function ($q) use ($employee) {
                $q->where('employee_id', $employee->id);
            });

        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function providerRatings(Provider $provider, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Rating::query()
            ->with(['booking', 'user'])
            ->whereHas('booking', function ($q) use ($provider, $filters) {
                $q->where('provider_id', $provider->id);

                if (!empty($filters['employee_uuid'])) {
                    $q->whereHas('employee', fn ($employeeQuery) => $employeeQuery->where('uuid', $filters['employee_uuid']));
                }

                if (!empty($filters['branch_uuid'])) {
                    $q->whereHas('branch', fn ($branchQuery) => $branchQuery->where('uuid', $filters['branch_uuid']));
                }
            });

        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['booking_uuid'])) {
            $query->whereHas('booking', fn ($q) => $q->where('uuid', $filters['booking_uuid']));
        }

        if (array_key_exists('active', $filters) && $filters['active'] !== null) {
            $query->where('active', (bool) $filters['active']);
        }

        if (!empty($filters['rating'])) {
            $query->where('rating', (int) $filters['rating']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }
    }
}
