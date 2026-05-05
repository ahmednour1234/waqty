<?php

namespace App\Repositories;

use App\Models\Banner;
use App\Repositories\Contracts\AdminBannerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminBannerRepository implements AdminBannerRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Banner::with('createdByAdmin');

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (!empty($filters['placement'])) {
            $query->where('placement', $filters['placement']);
        }

        if (!empty($filters['trashed']) && $filters['trashed'] === 'only') {
            $query->onlyTrashed();
        }

        return $query->orderBy('sort_order')->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findByUuid(string $uuid): ?Banner
    {
        return Banner::withTrashed()
            ->with('createdByAdmin')
            ->where('uuid', $uuid)
            ->first();
    }

    public function create(array $data): Banner
    {
        $banner = Banner::create($data);
        return $banner->load('createdByAdmin');
    }

    public function update(Banner $banner, array $data): Banner
    {
        $banner->update($data);
        return $banner->fresh('createdByAdmin');
    }

    public function delete(Banner $banner): void
    {
        $banner->delete();
    }
}
