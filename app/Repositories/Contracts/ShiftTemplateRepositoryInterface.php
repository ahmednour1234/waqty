<?php

namespace App\Repositories\Contracts;

use App\Models\ShiftTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ShiftTemplateRepositoryInterface
{
    public function findByUuid(string $uuid): ?ShiftTemplate;
    public function paginateProvider(int $providerId, array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function paginateAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function create(array $data): ShiftTemplate;
    public function update(ShiftTemplate $template, array $data): ShiftTemplate;
    public function softDelete(ShiftTemplate $template): bool;
    public function toggleActive(ShiftTemplate $template, bool $active): ShiftTemplate;
}
