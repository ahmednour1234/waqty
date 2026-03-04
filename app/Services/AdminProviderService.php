<?php

namespace App\Services;

use App\Models\Provider;
use App\Repositories\Contracts\ProviderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AdminProviderService
{
    public function __construct(
        private ProviderRepositoryInterface $providerRepository,
        private ImageUploadService $imageUploadService
    ) {
    }

    public function index(array $filters = [], int $perPage = 15)
    {
        return $this->providerRepository->paginateAdmin($filters, $perPage);
    }

    public function show(string $uuid): Provider
    {
        $provider = $this->providerRepository->findByUuid($uuid);

        if (!$provider) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Provider not found');
        }

        $provider->load(['category', 'country', 'city']);

        return $provider;
    }

    public function toggleActive(string $uuid, bool $active): Provider
    {
        $provider = $this->providerRepository->findByUuid($uuid);

        if (!$provider) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Provider not found');
        }

        return $this->providerRepository->toggleActive($provider, $active);
    }

    public function setBlocked(string $uuid, bool $blocked): Provider
    {
        $provider = $this->providerRepository->findByUuid($uuid);

        if (!$provider) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Provider not found');
        }

        return $this->providerRepository->setBlocked($provider, $blocked);
    }

    public function setBanned(string $uuid, bool $banned): Provider
    {
        $provider = $this->providerRepository->findByUuid($uuid);

        if (!$provider) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Provider not found');
        }

        $data = ['banned' => $banned];
        if ($banned) {
            $data['blocked'] = true;
            $data['active'] = false;
        }

        $provider->update($data);
        return $provider->fresh();
    }

    public function delete(string $uuid): bool
    {
        $provider = $this->providerRepository->findByUuid($uuid);

        if (!$provider) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Provider not found');
        }

        return $this->providerRepository->softDelete($provider);
    }

    public function restore(string $uuid): Provider
    {
        $provider = $this->providerRepository->restore($uuid);

        if (!$provider) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Provider not found');
        }

        return $provider;
    }

    public function forceDelete(string $uuid): bool
    {
        $provider = Provider::withTrashed()->whereUuid($uuid)->first();

        if (!$provider) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Provider not found');
        }

        if ($provider->logo_path) {
            $this->imageUploadService->deleteImage($provider->logo_path);
        }

        return $this->providerRepository->forceDelete($uuid);
    }
}
