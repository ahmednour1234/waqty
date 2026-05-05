<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Models\Rating;
use App\Repositories\Contracts\AdminRatingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminRatingRepository implements AdminRatingRepositoryInterface
{
    public function findByUuid(string $uuid): ?Rating
    {
        return Rating::withTrashed()
            ->where('uuid', $uuid)
            ->with(['user', 'booking.provider', 'booking.branch'])
            ->first();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Rating::withTrashed()
            ->with(['user', 'booking.provider', 'booking.branch']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (!empty($filters['trashed'])) {
            match ($filters['trashed']) {
                'only' => $query->onlyTrashed(),
                default => null,
            };
        } else {
            // Default: withTrashed already applied; refine to non-trashed unless requested
            $query->whereNull('ratings.deleted_at');
        }

        if (!empty($filters['rating'])) {
            $query->where('rating', (int) $filters['rating']);
        }

        if (!empty($filters['provider_uuid'])) {
            $query->whereHas('booking.provider', fn($q) => $q->where('uuid', $filters['provider_uuid']));
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function update(Rating $rating, array $data): Rating
    {
        $rating->update($data);
        return $rating->fresh(['user', 'booking.provider', 'booking.branch']);
    }

    public function delete(Rating $rating): void
    {
        $rating->delete();
    }

    public function stats(): array
    {
        $total     = Rating::whereNull('deleted_at')->count();
        $published = Rating::whereNull('deleted_at')->where('active', true)->count();
        $hidden    = Rating::whereNull('deleted_at')->where('active', false)->count();
        $avgRating = Rating::whereNull('deleted_at')->where('active', true)->avg('rating');

        return [
            'total'      => $total,
            'published'  => $published,
            'hidden'     => $hidden,
            'avg_rating' => $avgRating ? round((float) $avgRating, 1) : null,
        ];
    }

    public function analytics(): array
    {
        // Summary
        $total     = Rating::whereNull('deleted_at')->count();
        $published = Rating::whereNull('deleted_at')->where('active', true)->count();
        $hidden    = Rating::whereNull('deleted_at')->where('active', false)->count();
        $avgRating = Rating::whereNull('deleted_at')->avg('rating');

        // Response rate: completed bookings that have a rating / total completed bookings
        $completedBookings = Booking::where('status', Booking::STATUS_COMPLETED)->count();
        $responseRate = $completedBookings > 0
            ? round(($total / $completedBookings) * 100, 1)
            : 0;

        // Rating distribution (1–5 stars)
        $distribution = Rating::whereNull('deleted_at')
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating')
            ->pluck('count', 'rating');

        $ratingDistribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingDistribution[] = [
                'stars' => $i,
                'count' => (int) ($distribution[$i] ?? 0),
            ];
        }

        // Reviews by provider
        $byProvider = Rating::whereNull('ratings.deleted_at')
            ->join('bookings', 'ratings.booking_id', '=', 'bookings.id')
            ->join('providers', 'bookings.provider_id', '=', 'providers.id')
            ->selectRaw('providers.uuid as provider_uuid, providers.name as provider_name, COUNT(ratings.id) as total, ROUND(AVG(ratings.rating), 1) as avg_rating')
            ->groupBy('providers.id', 'providers.uuid', 'providers.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn($row) => [
                'provider_uuid' => $row->provider_uuid,
                'provider_name' => $row->provider_name,
                'total'         => (int) $row->total,
                'avg_rating'    => (float) $row->avg_rating,
            ])
            ->values()
            ->toArray();

        return [
            'summary' => [
                'total'         => $total,
                'avg_rating'    => $avgRating ? round((float) $avgRating, 1) : null,
                'published'     => $published,
                'hidden'        => $hidden,
                'response_rate' => $responseRate,
            ],
            'rating_distribution' => $ratingDistribution,
            'by_provider'         => $byProvider,
        ];
    }
}
