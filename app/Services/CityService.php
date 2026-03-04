<?php

namespace App\Services;

use App\Models\City;
use App\Models\Country;
use App\Repositories\Contracts\CityRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CityService
{
    public function __construct(
        private CityRepositoryInterface $cityRepository
    ) {
    }

    public function index(array $filters = [], int $perPage = 15)
    {
        return $this->cityRepository->paginate($filters, $perPage);
    }

    public function store(array $data): City
    {
        return DB::transaction(function () use ($data) {
            if (isset($data['country_uuid'])) {
                $country = Country::whereUuid($data['country_uuid'])->first();
                if (!$country) {
                    throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Country not found');
                }
                $data['country_id'] = $country->id;
                unset($data['country_uuid']);
            }

            return $this->cityRepository->create($data);
        });
    }

    public function show(string $uuid): City
    {
        $city = $this->cityRepository->findByUuid($uuid);

        if (!$city) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('City not found');
        }

        return $city;
    }

    public function update(string $uuid, array $data): City
    {
        return DB::transaction(function () use ($uuid, $data) {
            $city = $this->cityRepository->findByUuid($uuid);

            if (!$city) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('City not found');
            }

            if (isset($data['country_uuid'])) {
                $country = Country::whereUuid($data['country_uuid'])->first();
                if (!$country) {
                    throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Country not found');
                }
                $data['country_id'] = $country->id;
                unset($data['country_uuid']);
            }

            return $this->cityRepository->update($city, $data);
        });
    }

    public function destroy(string $uuid): bool
    {
        $city = $this->cityRepository->findByUuid($uuid);

        if (!$city) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('City not found');
        }

        return $this->cityRepository->delete($city);
    }

    public function restore(string $uuid): City
    {
        $city = $this->cityRepository->restore($uuid);

        if (!$city) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('City not found');
        }

        return $city;
    }

    public function forceDelete(string $uuid): bool
    {
        $city = City::withTrashed()->whereUuid($uuid)->first();

        if (!$city) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('City not found');
        }

        return $this->cityRepository->forceDelete($uuid);
    }

    public function toggleActive(string $uuid, bool $active): City
    {
        $city = $this->cityRepository->findByUuid($uuid);

        if (!$city) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('City not found');
        }

        return $this->cityRepository->update($city, ['active' => $active]);
    }
}
