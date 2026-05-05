<?php

namespace App\Repositories\Contracts;

use App\Models\Banner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdminBannerRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findByUuid(string $uuid): ?Banner;

    public function create(array $data): Banner;

    public function update(Banner $banner, array $data): Banner;

    public function delete(Banner $banner): void;
}
