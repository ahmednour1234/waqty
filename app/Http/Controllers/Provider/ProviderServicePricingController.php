<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Provider\ProviderPricingIndexRequest;
use App\Http\Requests\Provider\StoreServicePriceRequest;
use App\Http\Requests\Provider\UpdateServicePriceRequest;
use App\Http\Resources\Provider\ProviderServicePriceResource;
use App\Services\ProviderServicePricingService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Provider')]
#[Subgroup('Service Prices', 'Provider manages pricing rules per service scope')]
class ProviderServicePricingController extends Controller
{
    public function __construct(
        private ProviderServicePricingService $service,
    ) {}

    /**
     * List own service pricing rules.
     *
     * Returns all service prices owned by the authenticated provider.
     * Supports filtering by service, scope type, branch, employee, group, and active status.
     *
     * Scope types:
     * - default: global price for a service
     * - branch: price specific to one branch
     * - employee: price specific to one employee
     * - group: price specific to a pricing group
     *
     * Priority order when resolving: employee > group > branch > default
     *
     * @authenticated
     */
    public function index(ProviderPricingIndexRequest $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'service_uuid', 'sub_category_uuid', 'scope_type',
                'branch_uuid', 'employee_uuid', 'pricing_group_uuid', 'active',
            ]);

            $perPage  = (int) $request->input('per_page', 15);
            $paginated = $this->service->index($filters, $perPage);

            return ApiResponse::success(
                ProviderServicePriceResource::collection($paginated->items()),
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
     * Create a pricing rule.
     *
     * Creates a single service price record. Exactly one scope may be provided
     * (branch_uuid, employee_uuid, OR pricing_group_uuid). Omitting all three
     * creates the default/global price for that service.
     *
     * @authenticated
     */
    public function store(StoreServicePriceRequest $request): JsonResponse
    {
        try {
            $price = $this->service->store($request->validated());

            return ApiResponse::success(
                new ProviderServicePriceResource($price->load(['service', 'branch', 'employee', 'pricingGroup'])),
                'api.service_prices.created',
                201
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Show a single pricing rule.
     *
     * @authenticated
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $price = $this->service->show($uuid);

            return ApiResponse::success(
                new ProviderServicePriceResource($price->load(['service', 'branch', 'employee', 'pricingGroup']))
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.service_prices.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Update a pricing rule.
     *
     * @authenticated
     */
    public function update(UpdateServicePriceRequest $request, string $uuid): JsonResponse
    {
        try {
            $price = $this->service->update($uuid, $request->validated());

            return ApiResponse::success(
                new ProviderServicePriceResource($price->load(['service', 'branch', 'employee', 'pricingGroup'])),
                'api.service_prices.updated'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.service_prices.not_found', 404);
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Soft delete a pricing rule.
     *
     * @authenticated
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $this->service->destroy($uuid);
            return ApiResponse::success(null, 'api.service_prices.deleted');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.service_prices.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Toggle active status of a pricing rule.
     *
     * @authenticated
     */
    public function toggleActive(string $uuid): JsonResponse
    {
        try {
            $price = $this->service->toggleActive($uuid);

            return ApiResponse::success(
                new ProviderServicePriceResource($price->load(['service', 'branch', 'employee', 'pricingGroup'])),
                'api.general.updated'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.service_prices.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
