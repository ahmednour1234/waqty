<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\SetMainBranchRequest;
use App\Http\Requests\Provider\StoreProviderBranchRequest;
use App\Http\Requests\Provider\ToggleBranchActiveRequest;
use App\Http\Requests\Provider\UpdateProviderBranchRequest;
use App\Http\Resources\Provider\ProviderBranchResource;
use App\Http\Helpers\ApiResponse;
use App\Services\ProviderBranchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Provider')]
#[Subgroup('Branches', 'Provider branches CRUD')]
class ProviderBranchController extends Controller
{
    public function __construct(
        private ProviderBranchService $branchService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'search' => $request->input('search'),
                'active' => $request->has('active') ? filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN) : null,
                'is_main' => $request->has('is_main') ? filter_var($request->input('is_main'), FILTER_VALIDATE_BOOLEAN) : null,
                'city_uuid' => $request->input('city_uuid'),
                'country_uuid' => $request->input('country_uuid'),
            ];

            $perPage = (int) $request->input('per_page', 15);
            $paginated = $this->branchService->index($filters, $perPage);

            return ApiResponse::success(
                ProviderBranchResource::collection($paginated->items()),
                null,
                200,
                [
                    'pagination' => [
                        'current_page' => $paginated->currentPage(),
                        'per_page' => $paginated->perPage(),
                        'total' => $paginated->total(),
                        'last_page' => $paginated->lastPage(),
                    ],
                ]
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function store(StoreProviderBranchRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $logo = $request->file('logo');
            $branch = $this->branchService->store($data, $logo);

            return ApiResponse::success(
                new ProviderBranchResource($branch->load(['country', 'city'])),
                'api.branches.created',
                201
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function show(string $uuid): JsonResponse
    {
        try {
            $branch = $this->branchService->show($uuid);
            return ApiResponse::success(
                new ProviderBranchResource($branch->load(['country', 'city']))
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.branches.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateProviderBranchRequest $request, string $uuid): JsonResponse
    {
        try {
            $data = $request->validated();
            $logo = $request->file('logo');
            $branch = $this->branchService->update($uuid, $data, $logo);

            return ApiResponse::success(
                new ProviderBranchResource($branch->load(['country', 'city'])),
                'api.branches.updated'
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.branches.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function destroy(string $uuid): JsonResponse
    {
        try {
            $this->branchService->destroy($uuid);
            return ApiResponse::success(null, 'api.branches.deleted');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.branches.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function setMain(SetMainBranchRequest $request, string $uuid): JsonResponse
    {
        try {
            $branch = $this->branchService->setMain($uuid);
            return ApiResponse::success(
                new ProviderBranchResource($branch->load(['country', 'city'])),
                'api.branches.main_set'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.branches.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function toggleActive(ToggleBranchActiveRequest $request, string $uuid): JsonResponse
    {
        try {
            $branch = $this->branchService->toggleActive($uuid, $request->validated()['active']);

            return ApiResponse::success(
                new ProviderBranchResource($branch->load(['country', 'city'])),
                'api.branches.updated'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.branches.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
