<?php

namespace App\Repositories\Contracts;

use App\Models\Admin;

interface AdminRepositoryInterface
{
    public function findByEmail(string $email): ?Admin;

    public function findById(int $id): ?Admin;

    public function paginate(array $filters = [], int $perPage = 15);

    public function create(array $data): Admin;

    public function update(Admin $admin, array $data): Admin;
}
