<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdminUserRepositoryInterface
{
    public function findByUuid(string $uuid): ?User;

    public function findByUuidWithTrashed(string $uuid): ?User;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function update(User $user, array $data): User;

    public function delete(User $user): void;

    public function restore(string $uuid): User;
}
