<?php

namespace App\Repositories\Contracts;

use App\Models\Rating;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdminRatingRepositoryInterface
{
    public function findByUuid(string $uuid): ?Rating;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function update(Rating $rating, array $data): Rating;

    public function delete(Rating $rating): void;

    public function stats(): array;

    public function analytics(): array;
}
