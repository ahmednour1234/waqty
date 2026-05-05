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
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Admin')]
#[Subgroup('Service Pricing', 'Admin read-only oversight of pricing rules and groups')]
class AdminServicePricingController extends Controller
{
    public function __construct(
        private AdminServicePricingReadService $service,
    ) {}

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[QueryParam('provider_uuid', 'string', 'Filter by provider UUID.', required: false)]
    #[QueryParam('service_uuid', 'string', 'Filter by service UUID.', required: false)]
    #[QueryParam('sub_category_uuid', 'string', 'Filter by subcategory UUID.', required: false)]
    #[QueryParam('scope_type', 'string', 'Filter by scope type (branch or employee).', required: false, example: 'branch')]
    #[QueryParam('branch_uuid', 'string', 'Filter by branch UUID.', required: false)]
    #[QueryParam('employee_uuid', 'string', 'Filter by employee UUID.', required: false)]
    #[QueryParam('pricing_group_uuid', 'string', 'Filter by pricing group UUID.', required: false)]
    #[QueryParam('active', 'boolean', 'Filter by active status.', required: false)]
    #[QueryParam('trashed', 'string', 'Include soft-deleted records. Values: only, with.', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page (default 15).', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
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

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'data' => ['uuid' => '<UUID>', 'amount' => 100, 'scope_type' => 'branch', 'active' => true]], 200)]
    #[Response(['success' => false, 'message' => 'Not found'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
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

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[QueryParam('provider_uuid', 'string', 'Filter by provider UUID.', required: false)]
    #[QueryParam('active', 'boolean', 'Filter by active status.', required: false)]
    #[QueryParam('trashed', 'string', 'Include soft-deleted records. Values: only, with.', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page (default 15).', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
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

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'data' => ['uuid' => '<UUID>', 'name' => 'VIP Group', 'provider_uuid' => '<UUID>', 'active' => true]], 200)]
    #[Response(['success' => false, 'message' => 'Not found'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
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
