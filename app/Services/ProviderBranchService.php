<?php

namespace App\Services;

use App\Models\City;
use App\Models\Country;
use App\Models\Governorate;
use App\Models\Provider;
use App\Models\ProviderBranch;
use App\Repositories\Contracts\ProviderBranchRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProviderBranchService
{
    public function __construct(
        private ProviderBranchRepositoryInterface $branchRepository,
        private ImageUploadService $imageUploadService
    ) {
    }

    public function index(array $filters = [], int $perPage = 15)
    {
        $provider = Auth::guard('provider')->user();
        return $this->branchRepository->paginateProvider($provider->id, $filters, $perPage);
    }

    public function store(array $data, ?UploadedFile $logo = null): ProviderBranch
    {
        $provider = Auth::guard('provider')->user();

        return DB::transaction(function () use ($data, $logo, $provider) {
            $egypt = Country::where('iso2', 'EG')->first();
            if (!$egypt) {
                throw new \RuntimeException('Egypt country not found');
            }

            $data['provider_id'] = $provider->id;
            $data['country_id'] = $egypt->id;

            if (isset($data['city_uuid'])) {
                $city = City::whereUuid($data['city_uuid'])->first();
                if (!$city) {
                    throw new \Illuminate\Database\Eloquent\ModelNotFoundException('City not found');
                }
                $data['city_id'] = $city->id;
                unset($data['city_uuid']);
            }

            if (isset($data['governorate_uuid'])) {
                $governorate = Governorate::whereUuid($data['governorate_uuid'])->first();
                if (!$governorate) {
                    throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Governorate not found');
                }
                $data['governorate_id'] = $governorate->id;
                unset($data['governorate_uuid']);
            }

            $existingBranchesCount = ProviderBranch::where('provider_id', $provider->id)
                ->whereNull('deleted_at')
                ->count();

            if ($existingBranchesCount === 0) {
                $data['is_main'] = true;
            } elseif (isset($data['is_main']) && $data['is_main']) {
                ProviderBranch::where('provider_id', $provider->id)
                    ->where('id', '!=', 0)
                    ->update(['is_main' => false]);
            } else {
                $data['is_main'] = false;
            }

            $branch = $this->branchRepository->create($data);

            if ($logo) {
                $directory = 'providers/' . $provider->uuid . '/branches/' . $branch->uuid;
                $imagePath = $this->imageUploadService->processImage($logo, $directory);
                $branch = $this->branchRepository->update($branch, ['logo_path' => $imagePath]);
            }

            return $branch;
        });
    }

    public function show(string $uuid): ProviderBranch
    {
        $provider = Auth::guard('provider')->user();
        $branch = $this->branchRepository->findByUuid($uuid);

        if (!$branch) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Branch not found');
        }

        if ($branch->provider_id !== $provider->id) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Branch not found');
        }

        return $branch;
    }

    public function update(string $uuid, array $data, ?UploadedFile $logo = null): ProviderBranch
    {
        $provider = Auth::guard('provider')->user();

        return DB::transaction(function () use ($uuid, $data, $logo, $provider) {
            $branch = $this->branchRepository->findByUuid($uuid);

            if (!$branch) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Branch not found');
            }

            if ($branch->provider_id !== $provider->id) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Branch not found');
            }

            if (isset($data['city_uuid'])) {
                $city = City::whereUuid($data['city_uuid'])->first();
                if (!$city) {
                    throw new \Illuminate\Database\Eloquent\ModelNotFoundException('City not found');
                }
                $data['city_id'] = $city->id;
                unset($data['city_uuid']);
            }

            if (isset($data['governorate_uuid'])) {
                $governorate = Governorate::whereUuid($data['governorate_uuid'])->first();
                if (!$governorate) {
                    throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Governorate not found');
                }
                $data['governorate_id'] = $governorate->id;
                unset($data['governorate_uuid']);
            }

            if (isset($data['is_main']) && $data['is_main']) {
                ProviderBranch::where('provider_id', $provider->id)
                    ->where('id', '!=', $branch->id)
                    ->update(['is_main' => false]);
            }

            $oldLogoPath = $branch->logo_path;

            if ($logo) {
                $directory = 'providers/' . $provider->uuid . '/branches/' . $branch->uuid;
                $imagePath = $this->imageUploadService->processImage($logo, $directory);
                $data['logo_path'] = $imagePath;

                if ($oldLogoPath) {
                    $this->imageUploadService->deleteImage($oldLogoPath);
                }
            }

            return $this->branchRepository->update($branch, $data);
        });
    }

    public function destroy(string $uuid): bool
    {
        $provider = Auth::guard('provider')->user();
        $branch = $this->branchRepository->findByUuid($uuid);

        if (!$branch) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Branch not found');
        }

        if ($branch->provider_id !== $provider->id) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Branch not found');
        }

        if ($branch->is_main) {
            $nextBranch = ProviderBranch::where('provider_id', $provider->id)
                ->where('id', '!=', $branch->id)
                ->whereNull('deleted_at')
                ->first();

            if ($nextBranch) {
                DB::transaction(function () use ($nextBranch) {
                    $nextBranch->update(['is_main' => true]);
                });
            }
        }

        return $this->branchRepository->softDelete($branch);
    }

    public function setMain(string $uuid): ProviderBranch
    {
        $provider = Auth::guard('provider')->user();

        return DB::transaction(function () use ($uuid, $provider) {
            $branch = $this->branchRepository->findByUuid($uuid);

            if (!$branch) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Branch not found');
            }

            if ($branch->provider_id !== $provider->id) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Branch not found');
            }

            ProviderBranch::where('provider_id', $provider->id)
                ->where('id', '!=', $branch->id)
                ->update(['is_main' => false]);

            $branch->update(['is_main' => true]);

            return $branch->fresh();
        });
    }
}
