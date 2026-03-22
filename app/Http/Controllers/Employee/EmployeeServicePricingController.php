<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Resources\Employee\EmployeeServiceResolvedPriceResource;
use App\Models\Employee;
use App\Models\Service;
use App\Services\PriceResolverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Employee')]
#[Subgroup('Service Pricing', 'Employee-resolved service pricing (read-only)')]
class EmployeeServicePricingController extends Controller
{
    public function __construct(
        private PriceResolverService $resolver,
    ) {}

    /**
     * Resolve the final price for a service for the authenticated employee.
     *
     * Returns only the final resolved price. Does NOT expose internal pricing rules.
     * Priority: employee-specific → group → branch → default
     *
     * @authenticated
     */
    public function resolvePrice(Request $request, string $serviceUuid): JsonResponse
    {
        try {
            /** @var Employee $employee */
            $employee = Auth::guard('employee')->user();

            // Service must belong to employee's provider
            $service = Service::whereUuid($serviceUuid)
                ->where('active', true)
                ->whereNull('deleted_at')
                ->whereHas('providers', fn ($q) => $q
                    ->where('providers.id', $employee->provider_id)
                    ->whereNull('provider_service.deleted_at')
                )
                ->first();

            if (!$service) {
                return ApiResponse::error('api.services.not_found', 404);
            }

            $branchId = null;
            if ($request->has('branch_uuid')) {
                $branch = \App\Models\ProviderBranch::whereUuid($request->input('branch_uuid'))
                    ->where('provider_id', $employee->provider_id)
                    ->whereNull('deleted_at')
                    ->first();
                $branchId = $branch?->id;
            }

            $resolved = $this->resolver->getPrice($service->id, $employee->id, $branchId);

            if (!$resolved) {
                return ApiResponse::error('api.service_prices.no_price_found', 404);
            }

            // Enrich with service name
            $resolved['service_name'] = $service->name[app()->getLocale()] ?? $service->name['ar'] ?? null;
            $resolved['branch_uuid']  = $branchId ? ($branch?->uuid ?? null) : null;

            return ApiResponse::success(new EmployeeServiceResolvedPriceResource($resolved));
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
