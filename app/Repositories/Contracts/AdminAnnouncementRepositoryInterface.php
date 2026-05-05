<?php

namespace App\Repositories\Contracts;

use App\Models\Announcement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdminAnnouncementRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findByUuid(string $uuid): ?Announcement;

    public function create(array $data): Announcement;

    public function update(Announcement $announcement, array $data): Announcement;

    public function delete(Announcement $announcement): void;
}
