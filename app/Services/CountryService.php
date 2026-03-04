<?php

namespace App\Services;

use App\Models\Country;
use App\Repositories\Contracts\CountryRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CountryService
{
    public function __construct(
        private CountryRepositoryInterface $countryRepository
    ) {
    }

    public function index(array $filters = [], int $perPage = 15)
    {
        return $this->countryRepository->paginate($filters, $perPage);
    }

    public function store(array $data): Country
    {
        return DB::transaction(function () use ($data) {
            return $this->countryRepository->create($data);
        });
    }

    public function show(string $uuid): Country
    {
        $country = $this->countryRepository->findByUuid($uuid);

        if (!$country) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Country not found');
        }

        return $country;
    }

    public function update(string $uuid, array $data): Country
    {
        return DB::transaction(function () use ($uuid, $data) {
            $country = $this->countryRepository->findByUuid($uuid);

            if (!$country) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Country not found');
            }

            return $this->countryRepository->update($country, $data);
        });
    }

    public function destroy(string $uuid): bool
    {
        $country = $this->countryRepository->findByUuid($uuid);

        if (!$country) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Country not found');
        }

        return $this->countryRepository->delete($country);
    }

    public function restore(string $uuid): Country
    {
        $country = $this->countryRepository->restore($uuid);

        if (!$country) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Country not found');
        }

        return $country;
    }

    public function forceDelete(string $uuid): bool
    {
        $country = Country::withTrashed()->whereUuid($uuid)->first();

        if (!$country) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Country not found');
        }

        return $this->countryRepository->forceDelete($uuid);
    }

    public function toggleActive(string $uuid, bool $active): Country
    {
        $country = $this->countryRepository->findByUuid($uuid);

        if (!$country) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Country not found');
        }

        return $this->countryRepository->update($country, ['active' => $active]);
    }
}
