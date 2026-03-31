<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\ProviderBranch;
use App\Models\User;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaymentService
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepository
    ) {}

    public function storeForProvider(Provider $provider, array $data): Payment
    {
        $booking = Booking::whereUuid($data['booking_uuid'])
            ->where('provider_id', $provider->id)
            ->first();

        if (! $booking) {
            throw new \InvalidArgumentException(__('api.bookings.not_found'));
        }

        return $this->paymentRepository->create([
            'booking_id'     => $booking->id,
            'payment_method' => $data['payment_method'],
            'amount'         => $data['amount'] ?? $booking->price,
            'status'         => $data['status'] ?? Payment::STATUS_PENDING,
            'transaction_id' => $data['transaction_id'] ?? null,
            'notes'          => $data['notes'] ?? null,
        ]);
    }

    public function storeForBranch(ProviderBranch $branch, array $data): Payment
    {
        $booking = Booking::whereUuid($data['booking_uuid'])
            ->where('branch_id', $branch->id)
            ->first();

        if (! $booking) {
            throw new \InvalidArgumentException(__('api.bookings.not_found'));
        }

        return $this->paymentRepository->create([
            'booking_id'     => $booking->id,
            'payment_method' => $data['payment_method'],
            'amount'         => $data['amount'] ?? $booking->price,
            'status'         => $data['status'] ?? Payment::STATUS_PENDING,
            'transaction_id' => $data['transaction_id'] ?? null,
            'notes'          => $data['notes'] ?? null,
        ]);
    }

    public function update(Payment $payment, array $data): Payment
    {
        $fillable = array_filter([
            'payment_method' => $data['payment_method'] ?? null,
            'amount'         => $data['amount'] ?? null,
            'status'         => $data['status'] ?? null,
            'transaction_id' => array_key_exists('transaction_id', $data) ? $data['transaction_id'] : null,
            'notes'          => array_key_exists('notes', $data) ? $data['notes'] : null,
        ], fn($v) => $v !== null);

        return $this->paymentRepository->update($payment, $fillable);
    }

    public function destroy(Payment $payment): void
    {
        $this->paymentRepository->delete($payment);
    }

    public function indexAdmin(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->paymentRepository->paginateAdmin($filters, $perPage);
    }

    public function indexForProvider(Provider $provider, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->paymentRepository->paginateProvider($provider->id, $filters, $perPage);
    }

    public function indexForBranch(ProviderBranch $branch, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->paymentRepository->paginateBranch($branch->id, $filters, $perPage);
    }

    public function indexForUser(User $user, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->paymentRepository->paginateUser($user->id, $filters, $perPage);
    }

    public function indexForEmployee(Employee $employee, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->paymentRepository->paginateEmployee($employee->id, $filters, $perPage);
    }

    public function showForProvider(Provider $provider, string $uuid): Payment
    {
        $payment = $this->paymentRepository->findByUuidForProvider($uuid, $provider->id);

        if (! $payment) {
            throw new \InvalidArgumentException(__('api.payments.not_found'));
        }

        return $payment;
    }

    public function showForBranch(ProviderBranch $branch, string $uuid): Payment
    {
        $payment = $this->paymentRepository->findByUuidForBranch($uuid, $branch->id);

        if (! $payment) {
            throw new \InvalidArgumentException(__('api.payments.not_found'));
        }

        return $payment;
    }

    public function showForUser(User $user, string $uuid): Payment
    {
        $payment = $this->paymentRepository->findByUuidForUser($uuid, $user->id);

        if (! $payment) {
            throw new \InvalidArgumentException(__('api.payments.not_found'));
        }

        return $payment;
    }

    public function showForEmployee(Employee $employee, string $uuid): Payment
    {
        $payment = $this->paymentRepository->findByUuidForEmployee($uuid, $employee->id);

        if (! $payment) {
            throw new \InvalidArgumentException(__('api.payments.not_found'));
        }

        return $payment;
    }

    public function showAdmin(string $uuid): Payment
    {
        $payment = $this->paymentRepository->findByUuid($uuid);

        if (! $payment) {
            throw new \InvalidArgumentException(__('api.payments.not_found'));
        }

        return $payment;
    }
}
