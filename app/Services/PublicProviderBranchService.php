<?php

namespace App\Services;

use App\Models\ProviderBranch;
use App\Repositories\Contracts\ProviderBranchRepositoryInterface;

class PublicProviderBranchService
{
    public function __construct(
        private ProviderBranchRepositoryInterface $branchRepository
    ) {
    }

    public function index(array $filters = [])
    {
        return $this->branchRepository->listPublic($filters);
    }

    public function show(string $uuid): ProviderBranch
    {
        $branch = $this->branchRepository->findByUuid($uuid);

        if (!$branch) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Branch not found');
        }

        if (!$branch->active || $branch->blocked || $branch->banned) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Branch not found');
        }

        return $branch;
    }
}
