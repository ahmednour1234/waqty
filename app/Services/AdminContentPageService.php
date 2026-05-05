<?php

namespace App\Services;

use App\Models\ContentPage;
use App\Repositories\Contracts\AdminContentPageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class AdminContentPageService
{
    public function __construct(
        private AdminContentPageRepositoryInterface $repository,
    ) {}

    public function index(): Collection
    {
        return $this->repository->all();
    }

    public function show(string $uuid): ContentPage
    {
        $page = $this->repository->findByUuid($uuid);

        if (!$page) {
            throw new ModelNotFoundException('Content page not found.');
        }

        return $page;
    }

    public function create(array $data, int $adminId): ContentPage
    {
        $existing = $this->repository->findBySlug($data['slug']);

        if ($existing) {
            throw ValidationException::withMessages([
                'slug' => ['A page with this slug already exists.'],
            ]);
        }

        return $this->repository->create([
            'slug'                => $data['slug'],
            'title_en'            => $data['title_en'],
            'title_ar'            => $data['title_ar'],
            'content_en'          => $data['content_en'] ?? null,
            'content_ar'          => $data['content_ar'] ?? null,
            'active'              => $data['active'] ?? true,
            'updated_by_admin_id' => $adminId,
        ]);
    }

    public function update(string $uuid, array $data, int $adminId): ContentPage
    {
        $page = $this->show($uuid);

        // If slug is being changed, ensure no conflict
        if (isset($data['slug']) && $data['slug'] !== $page->slug) {
            $existing = $this->repository->findBySlug($data['slug']);
            if ($existing) {
                throw ValidationException::withMessages([
                    'slug' => ['A page with this slug already exists.'],
                ]);
            }
        }

        $updateData = array_filter([
            'slug'       => $data['slug']       ?? null,
            'title_en'   => $data['title_en']   ?? null,
            'title_ar'   => $data['title_ar']   ?? null,
            'content_en' => $data['content_en'] ?? null,
            'content_ar' => $data['content_ar'] ?? null,
        ], fn($v) => $v !== null);

        $updateData['updated_by_admin_id'] = $adminId;

        return $this->repository->update($page, $updateData);
    }

    public function setActive(string $uuid, bool $active, int $adminId): ContentPage
    {
        $page = $this->show($uuid);

        return $this->repository->update($page, [
            'active'              => $active,
            'updated_by_admin_id' => $adminId,
        ]);
    }
}
