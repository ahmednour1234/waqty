<?php

namespace App\Services;

use App\Models\Announcement;
use App\Repositories\Contracts\AdminAnnouncementRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminAnnouncementService
{
    public function __construct(
        private AdminAnnouncementRepositoryInterface $repository,
    ) {}

    public function index(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function show(string $uuid): Announcement
    {
        $announcement = $this->repository->findByUuid($uuid);

        if (!$announcement) {
            throw new ModelNotFoundException('Announcement not found.');
        }

        return $announcement;
    }

    public function create(array $data, int $adminId): Announcement
    {
        return $this->repository->create([
            'title_en'            => $data['title_en'],
            'title_ar'            => $data['title_ar'],
            'message_en'          => $data['message_en'],
            'message_ar'          => $data['message_ar'],
            'target'              => $data['target']   ?? 'all',
            'priority'            => $data['priority'] ?? 'normal',
            'active'              => $data['active']   ?? true,
            'ends_at'             => $data['ends_at']  ?? null,
            'created_by_admin_id' => $adminId,
        ]);
    }

    public function update(string $uuid, array $data): Announcement
    {
        $announcement = $this->show($uuid);

        $updateData = array_filter([
            'title_en'   => $data['title_en']   ?? null,
            'title_ar'   => $data['title_ar']   ?? null,
            'message_en' => $data['message_en'] ?? null,
            'message_ar' => $data['message_ar'] ?? null,
            'target'     => $data['target']     ?? null,
            'priority'   => $data['priority']   ?? null,
            'ends_at'    => $data['ends_at']    ?? null,
        ], fn($v) => $v !== null);

        return $this->repository->update($announcement, $updateData);
    }

    public function setActive(string $uuid, bool $active): Announcement
    {
        $announcement = $this->show($uuid);
        return $this->repository->update($announcement, ['active' => $active]);
    }

    public function destroy(string $uuid): void
    {
        $announcement = $this->show($uuid);
        $this->repository->delete($announcement);
    }
}
