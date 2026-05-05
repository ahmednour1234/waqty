<?php

namespace App\Repositories;

use App\Models\Announcement;
use App\Repositories\Contracts\AdminAnnouncementRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminAnnouncementRepository implements AdminAnnouncementRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Announcement::with('createdByAdmin');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title_en', 'like', "%{$search}%")
                  ->orWhere('title_ar', 'like', "%{$search}%")
                  ->orWhere('message_en', 'like', "%{$search}%")
                  ->orWhere('message_ar', 'like', "%{$search}%");
            });
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (!empty($filters['target'])) {
            $query->where('target', $filters['target']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['trashed']) && $filters['trashed'] === 'only') {
            $query->onlyTrashed();
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findByUuid(string $uuid): ?Announcement
    {
        return Announcement::withTrashed()
            ->with('createdByAdmin')
            ->where('uuid', $uuid)
            ->first();
    }

    public function create(array $data): Announcement
    {
        $announcement = Announcement::create($data);
        return $announcement->load('createdByAdmin');
    }

    public function update(Announcement $announcement, array $data): Announcement
    {
        $announcement->update($data);
        return $announcement->fresh('createdByAdmin');
    }

    public function delete(Announcement $announcement): void
    {
        $announcement->delete();
    }
}
