<?php

namespace App\Services;

use App\Models\Banner;
use App\Repositories\Contracts\AdminBannerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminBannerService
{
    public function __construct(
        private AdminBannerRepositoryInterface $repository,
    ) {}

    public function index(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function show(string $uuid): Banner
    {
        $banner = $this->repository->findByUuid($uuid);

        if (!$banner) {
            throw new ModelNotFoundException('Banner not found.');
        }

        return $banner;
    }

    public function create(array $data, int $adminId): Banner
    {
        $imagePath = null;

        if (!empty($data['image']) && $data['image'] instanceof UploadedFile) {
            $imagePath = $data['image']->store('banners', 'public');
        }

        return $this->repository->create([
            'title'               => $data['title'],
            'image_path'          => $imagePath,
            'placement'           => $data['placement']   ?? 'home_top',
            'dimensions'          => $data['dimensions']  ?? '1200x400',
            'active'              => $data['active']       ?? true,
            'sort_order'          => $data['sort_order']  ?? 0,
            'starts_at'           => $data['starts_at']   ?? null,
            'ends_at'             => $data['ends_at']     ?? null,
            'created_by_admin_id' => $adminId,
        ]);
    }

    public function update(string $uuid, array $data): Banner
    {
        $banner = $this->show($uuid);

        $updateData = array_filter([
            'title'      => $data['title']      ?? null,
            'placement'  => $data['placement']  ?? null,
            'dimensions' => $data['dimensions'] ?? null,
            'sort_order' => $data['sort_order'] ?? null,
            'starts_at'  => $data['starts_at']  ?? null,
            'ends_at'    => $data['ends_at']    ?? null,
        ], fn($v) => $v !== null);

        if (!empty($data['image']) && $data['image'] instanceof UploadedFile) {
            // Delete old image
            if ($banner->image_path) {
                Storage::disk('public')->delete($banner->image_path);
            }
            $updateData['image_path'] = $data['image']->store('banners', 'public');
        }

        return $this->repository->update($banner, $updateData);
    }

    public function setActive(string $uuid, bool $active): Banner
    {
        $banner = $this->show($uuid);
        return $this->repository->update($banner, ['active' => $active]);
    }

    public function destroy(string $uuid): void
    {
        $banner = $this->show($uuid);

        if ($banner->image_path) {
            Storage::disk('public')->delete($banner->image_path);
        }

        $this->repository->delete($banner);
    }
}
