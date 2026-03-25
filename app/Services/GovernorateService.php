<?php

namespace App\Services;

use App\Models\Governorate;
use App\Repositories\Contracts\GovernorateRepositoryInterface;
use Illuminate\Support\Facades\DB;

class GovernorateService
{
    public function __construct(
        private GovernorateRepositoryInterface $governorateRepository
    ) {
    }

    public function index(array $filters = [], int $perPage = 15)
    {
        return $this->governorateRepository->paginate($filters, $perPage);
    }

    public function all(array $filters = [])
    {
        return $this->governorateRepository->all($filters);
    }

    public function store(array $data): Governorate
    {
        return DB::transaction(function () use ($data) {
            return $this->governorateRepository->create($data);
        });
    }

    public function show(string $uuid): Governorate
    {
        $governorate = $this->governorateRepository->findByUuid($uuid);

        if (!$governorate) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Governorate not found');
        }

        return $governorate;
    }

    public function update(string $uuid, array $data): Governorate
    {
        return DB::transaction(function () use ($uuid, $data) {
            $governorate = $this->governorateRepository->findByUuid($uuid);

            if (!$governorate) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Governorate not found');
            }

            return $this->governorateRepository->update($governorate, $data);
        });
    }

    public function destroy(string $uuid): bool
    {
        $governorate = $this->governorateRepository->findByUuid($uuid);

        if (!$governorate) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Governorate not found');
        }

        return $this->governorateRepository->delete($governorate);
    }

    public function restore(string $uuid): Governorate
    {
        $governorate = $this->governorateRepository->restore($uuid);

        if (!$governorate) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Governorate not found');
        }

        return $governorate;
    }

    public function forceDelete(string $uuid): bool
    {
        $governorate = Governorate::withTrashed()->whereUuid($uuid)->first();

        if (!$governorate) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Governorate not found');
        }

        return $this->governorateRepository->forceDelete($uuid);
    }

    public function toggleActive(string $uuid, bool $active): Governorate
    {
        $governorate = $this->governorateRepository->findByUuid($uuid);

        if (!$governorate) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Governorate not found');
        }

        return $this->governorateRepository->update($governorate, ['active' => $active]);
    }
}
