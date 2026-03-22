<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Helpers\LocalizationHelper;
use App\Http\Resources\Public\PublicServiceResolvedPriceResource;
use App\Models\Provider;
use App\Models\Service;
use App\Services\PriceResolverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Public')]
#[Subgroup('Service Pricing', 'Public-facing resolved service price')]
class PublicServicePricingController extends Controller
{
    public function __construct(
        private PriceResolverService $resolver,
    ) {}

    /**
     * Resolve the final public price for a service.
     *
     * Returns only final price data. Never exposes internal pricing rule details.
     * Optionally pass branch_uuid or employee_uuid for context-specific pricing.
     *
     * @unauthenticated
     */
    public function resolvePrice(Request $request, string $serviceUuid): JsonResponse
    {
        try {
            $lang = LocalizationHelper::getCurrentLanguage($request);

            $query = Service::whereUuid($serviceUuid)
                ->where('active', true)
                ->whereNull('deleted_at');

            // If provider_uuid passed, narrow scope
            $providerId = null;
            if ($request->filled('provider_uuid')) {
                $provider = Provider::whereUuid($request->input('provider_uuid'))
                    ->where('active', true)
                    ->where('blocked', false)
                    ->where('banned', false)
                    ->whereNull('deleted_at')
                    ->first();

                if (!$provider) {
                    return ApiResponse::error('api.general.not_found', 404);
                }

                $providerId = $provider->id;
                $query->whereHas('providers', fn ($q) => $q
                    ->where('providers.id', $providerId)
                    ->whereNull('provider_service.deleted_at')
                );
            }

            $service = $query->with('providers')->first();

            if (!$service) {
                return ApiResponse::error('api.services.not_found', 404);
            }

            $branchId = null;
            if ($request->filled('branch_uuid')) {
                $branch = \App\Models\ProviderBranch::whereUuid($request->input('branch_uuid'))
                    ->whereNull('deleted_at')
                    ->first();
                $branchId = $branch?->id;
            }

            $employeeId = null;
            if ($request->filled('employee_uuid')) {
                $employee = \App\Models\Employee::whereUuid($request->input('employee_uuid'))
                    ->whereNull('deleted_at')
                    ->first();
                $employeeId = $employee?->id;
            }

            $resolved = $this->resolver->getPrice($service->id, $employeeId, $branchId);

            if (!$resolved) {
                return ApiResponse::error('api.service_prices.no_price_found', 404);
            }

            // Enrich for public output
            $resolved['service_name']  = $service->name[$lang] ?? $service->name['ar'] ?? null;
            $provider = $service->providers->first();
            $resolved['provider_name'] = $provider?->name;

            return ApiResponse::success(new PublicServiceResolvedPriceResource($resolved));
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
