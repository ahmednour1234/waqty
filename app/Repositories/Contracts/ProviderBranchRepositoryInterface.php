<?php

namespace App\Repositories\Contracts;

use App\Models\ProviderBranch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProviderBranchRepositoryInterface
{
    public function findByUuid(string $uuid): ?ProviderBranch;

    public function paginateAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function paginateProvider(int $providerId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function listPublic(array $filters = []): LengthAwarePaginator;

    public function create(array $data): ProviderBranch;

    public function update(ProviderBranch $branch, array $data): ProviderBranch;

    public function softDelete(ProviderBranch $branch): bool;

    public function restore(string $uuid): ?ProviderBranch;

    public function setStatus(ProviderBranch $branch, array $status): ProviderBranch;

    public function withTrashed(): self;
}
