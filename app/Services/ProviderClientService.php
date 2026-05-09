<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProviderClientService
{
    /**
     * Return a paginated list of distinct users who have booked with this provider.
     * Optionally filter by search (name/email/phone) or branch.
     */
    public function index(int $providerId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query()
            ->whereNull('users.deleted_at')
            ->whereExists(function ($sub) use ($providerId, $filters) {
                $sub->select(DB::raw(1))
                    ->from('bookings')
                    ->whereColumn('bookings.user_id', 'users.id')
                    ->where('bookings.provider_id', $providerId)
                    ->whereNull('bookings.deleted_at');

                if (! empty($filters['branch_uuid'])) {
                    $sub->whereExists(function ($b) use ($filters) {
                        $b->select(DB::raw(1))
                            ->from('provider_branches')
                            ->whereColumn('provider_branches.id', 'bookings.branch_id')
                            ->where('provider_branches.uuid', $filters['branch_uuid'])
                            ->whereNull('provider_branches.deleted_at');
                    });
                }
            })
            ->select([
                'users.id',
                'users.uuid',
                'users.name',
                'users.email',
                'users.phone',
                'users.image_path',
                'users.created_at',
                DB::raw("(SELECT COUNT(*) FROM bookings WHERE bookings.user_id = users.id AND bookings.provider_id = {$providerId} AND bookings.deleted_at IS NULL) as total_bookings"),
                DB::raw("(SELECT MAX(bookings.booking_date) FROM bookings WHERE bookings.user_id = users.id AND bookings.provider_id = {$providerId} AND bookings.deleted_at IS NULL) as last_booking_date"),
            ]);

        if (! empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', $search)
                  ->orWhere('users.email', 'like', $search)
                  ->orWhere('users.phone', 'like', $search);
            });
        }

        return $query->orderByDesc('last_booking_date')->paginate($perPage);
    }

    /**
     * Return full booking history for a single user under this provider.
     */
    public function bookingHistory(int $providerId, string $userUuid, int $perPage = 15): LengthAwarePaginator
    {
        $user = User::where('uuid', $userUuid)->whereNull('deleted_at')->firstOrFail();

        return Booking::where('provider_id', $providerId)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->with(['rating', 'latestPayment'])
            ->orderByDesc('booking_date')
            ->paginate($perPage);
    }
}
