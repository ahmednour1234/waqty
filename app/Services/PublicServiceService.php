<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\ProviderBranch;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Models\Subcategory;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PublicServiceService
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository
    ) {}

    public function index(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $resolved = $this->resolveFilterUuids($filters);
        return $this->serviceRepository->paginatePublic($resolved, $perPage);
    }

    public function show(string $uuid): Service
    {
        $service = $this->serviceRepository->findByUuid($uuid);

        if (!$service) {
            throw new ModelNotFoundException('Service not found');
        }

        $hasValidProvider = $service->providers()
            ->wherePivotNull('deleted_at')
            ->wherePivot('active', true)
            ->where('providers.active', true)
            ->where('providers.blocked', false)
            ->where('providers.banned', false)
            ->whereNull('providers.deleted_at')
            ->exists();

        if (!$hasValidProvider) {
            throw new ModelNotFoundException('Service not found');
        }

        return $service;
    }

    public function newest(int $perPage = 10): LengthAwarePaginator
    {
        return $this->serviceRepository->paginatePublicNewest($perPage);
    }

    public function nearest(float $lat, float $lng, float $radius, int $perPage = 10, bool $fallback = false): LengthAwarePaginator
    {
        if ($fallback) {
            return $this->serviceRepository->paginatePublicNewest($perPage);
        }

        return $this->serviceRepository->paginatePublicNearest($lat, $lng, $radius, $perPage);
    }

    public function showDetail(string $uuid, ?string $providerUuid, ?string $branchUuid): array
    {
        $service = Service::with(['subCategory'])->whereUuid($uuid)->whereNull('deleted_at')->first();

        if (!$service) {
            throw new ModelNotFoundException('Service not found');
        }

        $activeProviderQuery = fn ($q) => $q
            ->where('providers.active', true)
            ->where('providers.blocked', false)
            ->where('providers.banned', false)
            ->whereNull('providers.deleted_at')
            ->whereNull('provider_service.deleted_at')
            ->where('provider_service.active', true);

        if (!$service->providers()->where(fn ($q) => $activeProviderQuery($q))->exists()) {
            throw new ModelNotFoundException('Service not found');
        }

        $result = [
            'uuid'              => $service->uuid,
            'name'              => $service->name,
            'description'       => $service->description,
            'image_url'         => $service->image_path
                ? route('images.serve', ['type' => 'services', 'uuid' => $service->uuid])
                : null,
            'sub_category_uuid' => $service->subCategory?->uuid,
            'sub_category_name' => $service->subCategory?->name,
        ];

        if ($providerUuid === null) {
            $providers = $service->providers()
                ->where('providers.active', true)
                ->where('providers.blocked', false)
                ->where('providers.banned', false)
                ->whereNull('providers.deleted_at')
                ->whereNull('provider_service.deleted_at')
                ->where('provider_service.active', true)
                ->get();

            $providerIds = $providers->pluck('id')->toArray();
            $prices = ServicePrice::where('service_id', $service->id)
                ->whereIn('provider_id', $providerIds)
                ->whereNull('branch_id')
                ->whereNull('employee_id')
                ->whereNull('pricing_group_id')
                ->where('active', true)
                ->whereNull('deleted_at')
                ->get()
                ->keyBy('provider_id');

            $result['providers'] = $providers->map(fn ($p) => [
                'uuid'          => $p->uuid,
                'name'          => $p->name,
                'logo_url'      => $p->logo_path
                    ? route('images.serve', ['type' => 'providers', 'uuid' => $p->uuid])
                    : null,
                'default_price' => isset($prices[$p->id]) ? (string) $prices[$p->id]->price : null,
            ])->values()->toArray();

            return $result;
        }

        $provider = Provider::whereUuid($providerUuid)
            ->where('active', true)->where('blocked', false)->where('banned', false)
            ->whereNull('deleted_at')
            ->first();

        if (!$provider) {
            throw new ModelNotFoundException('Provider not found');
        }

        $offersService = $provider->services()
            ->where('services.id', $service->id)
            ->whereNull('provider_service.deleted_at')
            ->where('provider_service.active', true)
            ->exists();

        if (!$offersService) {
            throw new ModelNotFoundException('Service not found for this provider');
        }

        $defaultPrice = ServicePrice::where('service_id', $service->id)
            ->where('provider_id', $provider->id)
            ->whereNull('branch_id')
            ->whereNull('employee_id')
            ->whereNull('pricing_group_id')
            ->where('active', true)
            ->whereNull('deleted_at')
            ->first();

        $branches = ProviderBranch::where('provider_id', $provider->id)
            ->where('active', true)->where('blocked', false)->where('banned', false)
            ->whereNull('deleted_at')
            ->get();

        $result['provider'] = [
            'uuid'          => $provider->uuid,
            'name'          => $provider->name,
            'logo_url'      => $provider->logo_path
                ? route('images.serve', ['type' => 'providers', 'uuid' => $provider->uuid])
                : null,
        ];
        $result['default_price'] = $defaultPrice ? (string) $defaultPrice->price : null;
        $result['branches'] = $branches->map(fn ($b) => [
            'uuid'     => $b->uuid,
            'name'     => $b->name,
            'city'     => $b->city?->name ?? null,
            'latitude' => $b->latitude,
            'longitude' => $b->longitude,
            'is_main'  => (bool) $b->is_main,
            'logo_url' => $b->logo_path
                ? route('images.serve', ['type' => 'branches', 'uuid' => $b->uuid])
                : null,
        ])->values()->toArray();

        if ($branchUuid !== null) {
            $branch = $branches->firstWhere('uuid', $branchUuid);

            if (!$branch) {
                throw new ModelNotFoundException('Branch not found');
            }

            $employeePrices = ServicePrice::where('service_id', $service->id)
                ->where('provider_id', $provider->id)
                ->where('branch_id', $branch->id)
                ->whereNotNull('employee_id')
                ->whereNull('pricing_group_id')
                ->where('active', true)
                ->whereNull('deleted_at')
                ->with('employee:id,uuid,name')
                ->get();

            $employees = $employeePrices->map(fn ($ep) => [
                'uuid'            => $ep->employee?->uuid,
                'name'            => $ep->employee?->name,
                'effective_price' => (string) $ep->price,
            ])->filter(fn ($e) => $e['uuid'] !== null)->values()->toArray();

            $result['selected_branch'] = [
                'uuid'      => $branch->uuid,
                'name'      => $branch->name,
                'latitude'  => $branch->latitude,
                'longitude' => $branch->longitude,
            ];
            $result['employees']      = $employees;
            $result['effective_price'] = $defaultPrice ? (string) $defaultPrice->price : null;
        }

        return $result;
    }

    private function resolveFilterUuids(array $filters): array
    {
        if (!empty($filters['provider_uuid'])) {
            $provider = \App\Models\Provider::whereUuid($filters['provider_uuid'])->first();
            $filters['provider_id'] = $provider ? $provider->id : null;
            unset($filters['provider_uuid']);
        }

        if (!empty($filters['sub_category_uuid'])) {
            $sub = Subcategory::whereUuid($filters['sub_category_uuid'])->first();
            $filters['sub_category_id'] = $sub ? $sub->id : null;
            unset($filters['sub_category_uuid']);
        }

        return $filters;
    }
}
