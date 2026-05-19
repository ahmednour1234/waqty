<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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

    /**
     * Return a paginated list of clients with financial statement summaries.
     */
    public function statements(int $providerId, array $filters, int $perPage = 15): LengthAwarePaginator
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
                'users.created_at',
                DB::raw("(SELECT COUNT(*) FROM bookings WHERE bookings.user_id = users.id AND bookings.provider_id = {$providerId} AND bookings.deleted_at IS NULL) as total_bookings"),
                DB::raw("(SELECT COUNT(*) FROM bookings WHERE bookings.user_id = users.id AND bookings.provider_id = {$providerId} AND bookings.status = 'completed' AND bookings.deleted_at IS NULL) as completed_bookings"),
                DB::raw("(SELECT COUNT(*) FROM bookings WHERE bookings.user_id = users.id AND bookings.provider_id = {$providerId} AND bookings.status = 'cancelled' AND bookings.deleted_at IS NULL) as cancelled_bookings"),
                DB::raw("(SELECT COALESCE(SUM(bookings.price), 0) FROM bookings WHERE bookings.user_id = users.id AND bookings.provider_id = {$providerId} AND bookings.status = 'completed' AND bookings.deleted_at IS NULL) as total_charged"),
                DB::raw("(SELECT COALESCE(SUM(payments.amount), 0) FROM payments INNER JOIN bookings ON payments.booking_id = bookings.id WHERE bookings.user_id = users.id AND bookings.provider_id = {$providerId} AND payments.status = 'completed' AND payments.deleted_at IS NULL AND bookings.deleted_at IS NULL) as total_paid"),
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
     * Return a detailed financial statement for a single client under this provider.
     */
    public function statement(int $providerId, string $userUuid, int $perPage = 15): array
    {
        $user = User::where('uuid', $userUuid)->whereNull('deleted_at')->firstOrFail();

        $summary = Booking::where('provider_id', $providerId)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_bookings,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled_bookings,
                COALESCE(SUM(CASE WHEN status = ? THEN price ELSE 0 END), 0) as total_charged,
                MAX(booking_date) as last_booking_date
            ', [Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED, Booking::STATUS_COMPLETED])
            ->first();

        $totalPaid = Payment::whereIn('booking_id', function ($q) use ($providerId, $user) {
            $q->select('id')->from('bookings')
                ->where('provider_id', $providerId)
                ->where('user_id', $user->id)
                ->whereNull('deleted_at');
        })
            ->where('status', Payment::STATUS_COMPLETED)
            ->whereNull('deleted_at')
            ->sum('amount');

        $bookings = Booking::where('provider_id', $providerId)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->with(['latestPayment'])
            ->orderByDesc('booking_date')
            ->paginate($perPage);

        return compact('user', 'summary', 'totalPaid', 'bookings');
    }
}
