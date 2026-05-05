<?php

namespace App\Repositories\Contracts;

use App\Models\ContentPage;
use Illuminate\Database\Eloquent\Collection;

interface AdminContentPageRepositoryInterface
{
    public function all(): Collection;

    public function findByUuid(string $uuid): ?ContentPage;

    public function findBySlug(string $slug): ?ContentPage;

    public function create(array $data): ContentPage;

    public function update(ContentPage $page, array $data): ContentPage;
}
