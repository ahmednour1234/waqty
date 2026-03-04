<?php

namespace App\Services;

use App\Models\Admin;
use App\Repositories\Contracts\AdminRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    public function __construct(
        private AdminRepositoryInterface $adminRepository
    ) {
    }

    public function index(array $filters = [], int $perPage = 15)
    {
        return $this->adminRepository->paginate($filters, $perPage);
    }

    public function store(array $data): Admin
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->adminRepository->create($data);
    }

    public function show(int $id): Admin
    {
        $admin = $this->adminRepository->findById($id);

        if (!$admin) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Admin not found');
        }

        return $admin;
    }

    public function update(int $id, array $data): Admin
    {
        $admin = $this->adminRepository->findById($id);

        if (!$admin) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Admin not found');
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $this->adminRepository->update($admin, $data);
    }

    public function toggleActive(int $id, bool $active): Admin
    {
        $admin = $this->adminRepository->findById($id);

        if (!$admin) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Admin not found');
        }

        return $this->adminRepository->update($admin, ['active' => $active]);
    }
}
