<?php

namespace App\Repositories\Contracts;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PaymentRepositoryInterface
{
    public function findByUuid(string $uuid): ?Payment;

    public function findByUuidForProvider(string $uuid, int $providerId): ?Payment;

    public function findByUuidForBranch(string $uuid, int $branchId): ?Payment;

    public function findByUuidForUser(string $uuid, int $userId): ?Payment;

    public function findByUuidForEmployee(string $uuid, int $employeeId): ?Payment;

    public function paginateAdmin(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function paginateProvider(int $providerId, array $filters, int $perPage = 15): LengthAwarePaginator;

    public function paginateBranch(int $branchId, array $filters, int $perPage = 15): LengthAwarePaginator;

    public function paginateUser(int $userId, array $filters, int $perPage = 15): LengthAwarePaginator;

    public function paginateEmployee(int $employeeId, array $filters, int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Payment;

    public function update(Payment $payment, array $data): Payment;

    public function delete(Payment $payment): bool;
}
