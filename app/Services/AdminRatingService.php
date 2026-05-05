<?php

namespace App\Services;

use App\Models\Rating;
use App\Repositories\Contracts\AdminRatingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminRatingService
{
    public function __construct(
        private AdminRatingRepositoryInterface $ratingRepository,
    ) {}

    public function index(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->ratingRepository->paginate($filters, $perPage);
    }

    public function show(string $uuid): Rating
    {
        $rating = $this->ratingRepository->findByUuid($uuid);

        if (!$rating) {
            throw new ModelNotFoundException('Rating not found.');
        }

        return $rating;
    }

    public function setActive(string $uuid, bool $active): Rating
    {
        $rating = $this->show($uuid);
        return $this->ratingRepository->update($rating, ['active' => $active]);
    }

    public function destroy(string $uuid): void
    {
        $rating = $this->show($uuid);
        $this->ratingRepository->delete($rating);
    }

    public function stats(): array
    {
        return $this->ratingRepository->stats();
    }

    public function analytics(): array
    {
        return $this->ratingRepository->analytics();
    }
}
