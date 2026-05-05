<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\AdminUserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminUserService
{
    public function __construct(
        private AdminUserRepositoryInterface $userRepository,
    ) {}

    public function index(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($filters, $perPage);
    }

    public function show(string $uuid): User
    {
        $user = $this->userRepository->findByUuid($uuid);

        if (!$user) {
            throw new ModelNotFoundException('User not found.');
        }

        return $user;
    }

    public function setActive(string $uuid, bool $active): User
    {
        $user = $this->show($uuid);
        return $this->userRepository->update($user, ['active' => $active]);
    }

    public function setBlocked(string $uuid, bool $blocked): User
    {
        $user = $this->show($uuid);
        return $this->userRepository->update($user, ['blocked' => $blocked]);
    }

    public function setBanned(string $uuid, bool $banned): User
    {
        $user = $this->show($uuid);
        return $this->userRepository->update($user, ['banned' => $banned]);
    }

    public function destroy(string $uuid): void
    {
        $user = $this->show($uuid);
        $this->userRepository->delete($user);
    }

    public function restore(string $uuid): User
    {
        return $this->userRepository->restore($uuid);
    }
}
