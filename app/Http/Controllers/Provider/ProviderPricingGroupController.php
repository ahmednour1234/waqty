<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Provider\StorePricingGroupRequest;
use App\Http\Requests\Provider\UpdatePricingGroupRequest;
use App\Http\Requests\Provider\SyncPricingGroupEmployeesRequest;
use App\Http\Resources\Provider\ProviderPricingGroupResource;
use App\Services\ProviderPricingGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Provider')]
#[Subgroup('Pricing Groups', 'Provider manages employee pricing groups')]
class ProviderPricingGroupController extends Controller
{
    public function __construct(
        private ProviderPricingGroupService $service,
    ) {}

    /**
     * List own pricing groups.
     *
     * @authenticated
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters  = ['active' => $request->has('active') ? filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null];
            $perPage  = (int) $request->input('per_page', 15);
            $paginated = $this->service->index($filters, $perPage);

            return ApiResponse::success(
                ProviderPricingGroupResource::collection($paginated->items()),
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
     * Create a pricing group.
     *
     * Optionally assign multiple employees in one request via employee_uuids[].
     * All employees must belong to the authenticated provider.
     *
     * @authenticated
     */
    public function store(StorePricingGroupRequest $request): JsonResponse
    {
        try {
            $group = $this->service->store($request->validated());

            return ApiResponse::success(
                new ProviderPricingGroupResource($group->loadCount('employees')),
                'api.pricing_groups.created',
                201
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Show a single pricing group.
     *
     * @authenticated
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $group = $this->service->show($uuid);

            return ApiResponse::success(
                new ProviderPricingGroupResource($group->loadCount('employees'))
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.pricing_groups.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Update a pricing group.
     *
     * Providing employee_uuids will sync the group members (replaces current list).
     *
     * @authenticated
     */
    public function update(UpdatePricingGroupRequest $request, string $uuid): JsonResponse
    {
        try {
            $group = $this->service->update($uuid, $request->validated());

            return ApiResponse::success(
                new ProviderPricingGroupResource($group->loadCount('employees')),
                'api.pricing_groups.updated'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.pricing_groups.not_found', 404);
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Soft delete a pricing group.
     *
     * @authenticated
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $this->service->destroy($uuid);
            return ApiResponse::success(null, 'api.pricing_groups.deleted');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.pricing_groups.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Toggle active status of a pricing group.
     *
     * @authenticated
     */
    public function toggleActive(string $uuid): JsonResponse
    {
        try {
            $group = $this->service->toggleActive($uuid);

            return ApiResponse::success(
                new ProviderPricingGroupResource($group),
                'api.general.updated'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.pricing_groups.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Sync all employees in a pricing group.
     *
     * Replaces the entire current employee list with the provided one.
     * Send an empty array to remove all members.
     *
     * @authenticated
     */
    public function syncEmployees(SyncPricingGroupEmployeesRequest $request, string $uuid): JsonResponse
    {
        try {
            $group = $this->service->syncEmployees($uuid, $request->input('employee_uuids', []));

            return ApiResponse::success(
                new ProviderPricingGroupResource($group->loadCount('employees')),
                'api.pricing_groups.employees_synced'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.pricing_groups.not_found', 404);
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Add employees to a pricing group.
     *
     * Adds employees without removing existing ones.
     *
     * @authenticated
     */
    public function addEmployees(SyncPricingGroupEmployeesRequest $request, string $uuid): JsonResponse
    {
        try {
            $group = $this->service->addEmployees($uuid, $request->input('employee_uuids', []));

            return ApiResponse::success(
                new ProviderPricingGroupResource($group->loadCount('employees')),
                'api.pricing_groups.employees_added'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.pricing_groups.not_found', 404);
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Remove employees from a pricing group.
     *
     * @authenticated
     */
    public function removeEmployees(SyncPricingGroupEmployeesRequest $request, string $uuid): JsonResponse
    {
        try {
            $group = $this->service->removeEmployees($uuid, $request->input('employee_uuids', []));

            return ApiResponse::success(
                new ProviderPricingGroupResource($group->loadCount('employees')),
                'api.pricing_groups.employees_removed'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.pricing_groups.not_found', 404);
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
