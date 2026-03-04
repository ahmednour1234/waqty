<?php

namespace App\Services;

use App\Models\ProviderBranch;
use App\Repositories\Contracts\ProviderBranchRepositoryInterface;

class AdminProviderBranchService
{
    public function __construct(
        private ProviderBranchRepositoryInterface $branchRepository
    ) {
    }

    public function index(array $filters = [], int $perPage = 15)
    {
        return $this->branchRepository->paginateAdmin($filters, $perPage);
    }

    public function show(string $uuid): ProviderBranch
    {
        $branch = $this->branchRepository->findByUuid($uuid);

        if (!$branch) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Branch not found');
        }

        return $branch;
    }

    public function updateStatus(string $uuid, array $status): ProviderBranch
    {
        $branch = $this->branchRepository->findByUuid($uuid);

        if (!$branch) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Branch not found');
        }

        return $this->branchRepository->setStatus($branch, $status);
    }

    public function destroy(string $uuid): bool
    {
        $branch = $this->branchRepository->findByUuid($uuid);

        if (!$branch) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Branch not found');
        }

        return $this->branchRepository->softDelete($branch);
    }

    public function restore(string $uuid): ProviderBranch
    {
        $branch = $this->branchRepository->restore($uuid);

        if (!$branch) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Branch not found');
        }

        return $branch;
    }
}
