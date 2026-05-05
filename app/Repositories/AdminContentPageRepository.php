<?php

namespace App\Repositories;

use App\Models\ContentPage;
use App\Repositories\Contracts\AdminContentPageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AdminContentPageRepository implements AdminContentPageRepositoryInterface
{
    public function all(): Collection
    {
        return ContentPage::with('updatedByAdmin')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function findByUuid(string $uuid): ?ContentPage
    {
        return ContentPage::with('updatedByAdmin')
            ->where('uuid', $uuid)
            ->first();
    }

    public function findBySlug(string $slug): ?ContentPage
    {
        return ContentPage::with('updatedByAdmin')
            ->where('slug', $slug)
            ->first();
    }

    public function create(array $data): ContentPage
    {
        $page = ContentPage::create($data);
        return $page->load('updatedByAdmin');
    }

    public function update(ContentPage $page, array $data): ContentPage
    {
        $page->update($data);
        return $page->fresh('updatedByAdmin');
    }
}
