<?php

namespace App\Repositories;

use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function findByUuid(string $uuid): ?Payment
    {
        return Payment::whereUuid($uuid)->with('booking')->first();
    }

    public function findByUuidForProvider(string $uuid, int $providerId): ?Payment
    {
        return Payment::whereUuid($uuid)
            ->whereHas('booking', fn($q) => $q->where('provider_id', $providerId))
            ->with('booking')
            ->first();
    }

    public function findByUuidForBranch(string $uuid, int $branchId): ?Payment
    {
        return Payment::whereUuid($uuid)
            ->whereHas('booking', fn($q) => $q->where('branch_id', $branchId))
            ->with('booking')
            ->first();
    }

    public function findByUuidForUser(string $uuid, int $userId): ?Payment
    {
        return Payment::whereUuid($uuid)
            ->whereHas('booking', fn($q) => $q->where('user_id', $userId))
            ->with('booking')
            ->first();
    }

    public function findByUuidForEmployee(string $uuid, int $employeeId): ?Payment
    {
        return Payment::whereUuid($uuid)
            ->whereHas('booking', fn($q) => $q->where('employee_id', $employeeId))
            ->with('booking')
            ->first();
    }

    public function paginateAdmin(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Payment::with('booking');

        if (!empty($filters['trashed']) && $filters['trashed'] === 'only') {
            $query->onlyTrashed();
        } elseif (!empty($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        }

        $this->applyCommonFilters($query, $filters);

        if (!empty($filters['booking_uuid'])) {
            $query->whereHas('booking', fn($q) => $q->where('uuid', $filters['booking_uuid']));
        }

        if (!empty($filters['provider_uuid'])) {
            $query->whereHas('booking', fn($q) => $q->whereHas('provider', fn($p) => $p->where('uuid', $filters['provider_uuid'])));
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function paginateProvider(int $providerId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Payment::whereHas('booking', fn($q) => $q->where('provider_id', $providerId))
            ->with('booking');

        $this->applyCommonFilters($query, $filters);

        if (!empty($filters['booking_uuid'])) {
            $query->whereHas('booking', fn($q) => $q->where('uuid', $filters['booking_uuid']));
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function paginateBranch(int $branchId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Payment::whereHas('booking', fn($q) => $q->where('branch_id', $branchId))
            ->with('booking');

        $this->applyCommonFilters($query, $filters);

        if (!empty($filters['booking_uuid'])) {
            $query->whereHas('booking', fn($q) => $q->where('uuid', $filters['booking_uuid']));
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function paginateUser(int $userId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Payment::whereHas('booking', fn($q) => $q->where('user_id', $userId))
            ->with('booking');

        $this->applyCommonFilters($query, $filters);

        if (!empty($filters['booking_uuid'])) {
            $query->whereHas('booking', fn($q) => $q->where('uuid', $filters['booking_uuid']));
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function paginateEmployee(int $employeeId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Payment::whereHas('booking', fn($q) => $q->where('employee_id', $employeeId))
            ->with('booking');

        $this->applyCommonFilters($query, $filters);

        if (!empty($filters['booking_uuid'])) {
            $query->whereHas('booking', fn($q) => $q->where('uuid', $filters['booking_uuid']));
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    public function update(Payment $payment, array $data): Payment
    {
        $payment->fill($data);
        $payment->save();
        return $payment->fresh();
    }

    public function delete(Payment $payment): bool
    {
        return (bool) $payment->delete();
    }

    private function applyCommonFilters($query, array $filters): void
    {
        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date'] . ' 23:59:59');
        }
    }
}
