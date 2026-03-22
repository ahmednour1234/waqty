<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\Service;
use App\Models\Subcategory;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ProviderServiceService
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
        private ImageUploadService $imageUploadService
    ) {}

    public function index(Provider $provider, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $resolved = $this->resolveSubCategoryFilter($filters);
        return $this->serviceRepository->paginateProvider($provider->id, $resolved, $perPage);
    }

    public function store(Provider $provider, array $data, ?UploadedFile $image = null): Service
    {
        return DB::transaction(function () use ($provider, $data, $image) {
            unset($data['active']);

            if (!empty($data['sub_category_uuid'])) {
                $sub = Subcategory::whereUuid($data['sub_category_uuid'])->first();
                if (!$sub) {
                    throw new ModelNotFoundException('SubCategory not found');
                }
                $data['sub_category_id'] = $sub->id;
                unset($data['sub_category_uuid']);
            }

            $service = $this->serviceRepository->create($data);
            $this->serviceRepository->attachProvider($service, $provider->id);

            if ($image) {
                $directory = "providers/{$provider->uuid}/services/{$service->uuid}";
                $imagePath = $this->imageUploadService->processImage($image, $directory);
                $service = $this->serviceRepository->update($service, ['image_path' => $imagePath]);
            } else {
                $service->load(['providers', 'subCategory']);
            }

            return $service;
        });
    }

    public function show(Provider $provider, string $uuid): Service
    {
        $service = $this->serviceRepository->findByUuid($uuid);
        $this->assertOwnership($service, $provider);
        return Service::with([
            'subCategory',
            'providers' => fn ($q) => $q->where('providers.id', $provider->id),
        ])->where('uuid', $uuid)->firstOrFail();
    }

    public function update(Provider $provider, string $uuid, array $data, ?UploadedFile $image = null): Service
    {
        return DB::transaction(function () use ($provider, $uuid, $data, $image) {
            $service = $this->serviceRepository->findByUuid($uuid);
            $this->assertOwnership($service, $provider);

            $pivotData = [];

            if (array_key_exists('name', $data)) {
                $pivotData['name'] = $data['name'];
            }
            if (array_key_exists('description', $data)) {
                $pivotData['description'] = $data['description'];
            }

            if (!empty($data['sub_category_uuid'])) {
                $sub = Subcategory::whereUuid($data['sub_category_uuid'])->first();
                if (!$sub) {
                    throw new ModelNotFoundException('SubCategory not found');
                }
                $pivotData['sub_category_id'] = $sub->id;
            }

            if ($image) {
                $pivotProvider = $service->providers->firstWhere('id', $provider->id);
                $oldPivotImagePath = $pivotProvider?->pivot?->image_path;
                $directory = "providers/{$provider->uuid}/services/{$service->uuid}/pivot";
                $pivotData['image_path'] = $this->imageUploadService->processImage($image, $directory);
                if ($oldPivotImagePath) {
                    $this->imageUploadService->deleteImage($oldPivotImagePath);
                }
            }

            if (isset($data['active'])) {
                $this->serviceRepository->togglePivotActive($service, $provider->id, (bool) $data['active']);
            }

            if (!empty($pivotData)) {
                $this->serviceRepository->updatePivotOverrides($service, $provider->id, $pivotData);
            }

            return Service::with([
                'subCategory',
                'providers' => fn ($q) => $q->where('providers.id', $provider->id),
            ])->where('uuid', $uuid)->firstOrFail();
        });
    }

    public function destroy(Provider $provider, string $uuid): void
    {
        $service = $this->serviceRepository->findByUuid($uuid);
        $this->assertOwnership($service, $provider);
        $this->serviceRepository->softDeletePivot($service, $provider->id);
    }

    public function toggleActive(Provider $provider, string $uuid, bool $active): Service
    {
        $service = $this->serviceRepository->findByUuid($uuid);
        $this->assertOwnership($service, $provider);
        return $this->serviceRepository->togglePivotActive($service, $provider->id, $active);
    }

    private function assertOwnership(?Service $service, Provider $provider): void
    {
        if (!$service) {
            throw new ModelNotFoundException('Service not found');
        }
        if (!$this->serviceRepository->isAttachedToProvider($service, $provider->id)) {
            throw new \Illuminate\Auth\Access\AuthorizationException('api.services.unauthorized');
        }
    }

    private function resolveSubCategoryFilter(array $filters): array
    {
        if (!empty($filters['sub_category_uuid'])) {
            $sub = Subcategory::whereUuid($filters['sub_category_uuid'])->first();
            $filters['sub_category_id'] = $sub ? $sub->id : null;
            unset($filters['sub_category_uuid']);
        }
        return $filters;
    }
}
