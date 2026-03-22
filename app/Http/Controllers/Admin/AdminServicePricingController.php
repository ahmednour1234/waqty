<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Admin\AdminPricingIndexRequest;
use App\Http\Resources\Admin\AdminPricingGroupResource;
use App\Http\Resources\Admin\AdminServicePriceResource;
use App\Services\AdminServicePricingReadService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Admin')]
#[Subgroup('Service Pricing', 'Admin read-only oversight of pricing rules and groups')]
class AdminServicePricingController extends Controller
{
    public function __construct(
        private AdminServicePricingReadService $service,
    ) {}

    /**
     * List all service pricing rules.
     *
     * Admin has unrestricted read access. Filter by provider, service, scope, etc.
     *
     * @authenticated
     */
    public function indexPrices(AdminPricingIndexRequest $request): JsonResponse
    {
        try {
            $filters  = $request->only([
                'provider_uuid', 'service_uuid', 'sub_category_uuid', 'scope_type',
                'branch_uuid', 'employee_uuid', 'pricing_group_uuid', 'active', 'trashed',
            ]);
            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->service->listPrices($filters, $perPage);

            return ApiResponse::success(
                AdminServicePriceResource::collection($paginated->items()),
                null,
                200,
                [
                    'pagination' => [
                        'current_page' => $paginated->currentPage(),
                        'per_page'     => $paginated->perPage(),
                        'total'        => $paginated->total(),
                        'last_page'    => $paginated->lastPage(),
                    ],
                ]
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Show a single service pricing rule.
     *
     * @authenticated
     */
    public function showPrice(string $uuid): JsonResponse
    {
        try {
            $price = $this->service->showPrice($uuid);
            return ApiResponse::success(
                new AdminServicePriceResource($price->load(['provider', 'service', 'branch', 'employee', 'pricingGroup']))
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.service_prices.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * List all pricing groups.
     *
     * @authenticated
     */
    public function indexGroups(AdminPricingIndexRequest $request): JsonResponse
    {
        try {
            $filters  = $request->only(['provider_uuid', 'active', 'trashed']);
            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->service->listGroups($filters, $perPage);

            return ApiResponse::success(
                AdminPricingGroupResource::collection($paginated->items()),
                null,
                200,
                [
                    'pagination' => [
                        'current_page' => $paginated->currentPage(),
                        'per_page'     => $paginated->perPage(),
                        'total'        => $paginated->total(),
                        'last_page'    => $paginated->lastPage(),
                    ],
                ]
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Show a single pricing group.
     *
     * @authenticated
     */
    public function showGroup(string $uuid): JsonResponse
    {
        try {
            $group = $this->service->showGroup($uuid);
            return ApiResponse::success(
                new AdminPricingGroupResource($group->load(['provider', 'employees'])->loadCount('employees'))
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.pricing_groups.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
