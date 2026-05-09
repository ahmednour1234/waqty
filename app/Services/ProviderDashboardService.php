<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\ProviderBranch;
use App\Models\Rating;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProviderDashboardService
{
    public function getStats(int $providerId): array
    {
        $today = Carbon::today()->toDateString();

        return [
            'bookings'  => $this->bookingStats($providerId, $today),
            'revenue'   => $this->revenueStats($providerId, $today),
            'employees' => $this->employeeStats($providerId),
            'branches'  => $this->branchStats($providerId),
            'ratings'   => $this->ratingStats($providerId),
            'payments'  => $this->paymentStats($providerId),
        ];
    }

    private function bookingStats(int $providerId, string $today): array
    {
        $counts = Booking::where('provider_id', $providerId)
            ->whereNull('deleted_at')
            ->selectRaw(
                'COUNT(*) as total,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as confirmed,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as no_show,
                 SUM(CASE WHEN DATE(booking_date) = ? THEN 1 ELSE 0 END) as today',
                [
                    Booking::STATUS_PENDING,
                    Booking::STATUS_CONFIRMED,
                    Booking::STATUS_COMPLETED,
                    Booking::STATUS_CANCELLED,
                    Booking::STATUS_NO_SHOW,
                    $today,
                ]
            )
            ->first();

        return [
            'total'     => (int) $counts->total,
            'by_status' => [
                'pending'   => (int) $counts->pending,
                'confirmed' => (int) $counts->confirmed,
                'completed' => (int) $counts->completed,
                'cancelled' => (int) $counts->cancelled,
                'no_show'   => (int) $counts->no_show,
            ],
            'today' => (int) $counts->today,
        ];
    }

    private function revenueStats(int $providerId, string $today): array
    {
        $revenue = Booking::where('provider_id', $providerId)
            ->where('status', Booking::STATUS_COMPLETED)
            ->whereNull('deleted_at')
            ->selectRaw(
                'COALESCE(SUM(price), 0) as total,
                 COALESCE(SUM(CASE WHEN DATE(booking_date) = ? THEN price ELSE 0 END), 0) as today',
                [$today]
            )
            ->first();

        return [
            'total' => (float) $revenue->total,
            'today' => (float) $revenue->today,
        ];
    }

    private function employeeStats(int $providerId): array
    {
        $counts = Employee::where('provider_id', $providerId)
            ->whereNull('deleted_at')
            ->selectRaw(
                'COUNT(*) as total,
                 SUM(CASE WHEN active = 1 AND blocked = 0 THEN 1 ELSE 0 END) as active,
                 SUM(CASE WHEN blocked = 1 THEN 1 ELSE 0 END) as blocked'
            )
            ->first();

        return [
            'total'   => (int) $counts->total,
            'active'  => (int) $counts->active,
            'blocked' => (int) $counts->blocked,
        ];
    }

    private function branchStats(int $providerId): array
    {
        $counts = ProviderBranch::where('provider_id', $providerId)
            ->whereNull('deleted_at')
            ->selectRaw(
                'COUNT(*) as total,
                 SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as active'
            )
            ->first();

        return [
            'total'  => (int) $counts->total,
            'active' => (int) $counts->active,
        ];
    }

    private function ratingStats(int $providerId): array
    {
        $stats = Rating::whereNull('ratings.deleted_at')
            ->join('bookings', 'ratings.booking_id', '=', 'bookings.id')
            ->where('bookings.provider_id', $providerId)
            ->whereNull('bookings.deleted_at')
            ->selectRaw('COUNT(ratings.id) as total, ROUND(AVG(ratings.rating), 2) as average')
            ->first();

        return [
            'total'   => (int) $stats->total,
            'average' => $stats->average !== null ? (float) $stats->average : null,
        ];
    }

    private function paymentStats(int $providerId): array
    {
        $stats = Payment::whereNull('payments.deleted_at')
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->where('bookings.provider_id', $providerId)
            ->whereNull('bookings.deleted_at')
            ->where('payments.status', Payment::STATUS_COMPLETED)
            ->selectRaw('COALESCE(SUM(payments.amount), 0) as total_collected')
            ->first();

        return [
            'total_collected' => (float) $stats->total_collected,
        ];
    }
}
