<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\AdminUserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminUserRepository implements AdminUserRepositoryInterface
{
    public function findByUuid(string $uuid): ?User
    {
        return User::where('uuid', $uuid)->first();
    }

    public function findByUuidWithTrashed(string $uuid): ?User
    {
        return User::withTrashed()->where('uuid', $uuid)->first();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (isset($filters['blocked'])) {
            $query->where('blocked', $filters['blocked']);
        }

        if (isset($filters['banned'])) {
            $query->where('banned', $filters['banned']);
        }

        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (!empty($filters['trashed'])) {
            match ($filters['trashed']) {
                'only' => $query->onlyTrashed(),
                'with' => $query->withTrashed(),
                default => null,
            };
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function restore(string $uuid): User
    {
        $user = User::withTrashed()->where('uuid', $uuid)->firstOrFail();
        $user->restore();
        return $user->fresh();
    }
}
