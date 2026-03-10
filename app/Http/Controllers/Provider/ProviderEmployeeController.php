<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\BlockEmployeeRequest;
use App\Http\Requests\Provider\StoreEmployeeRequest;
use App\Http\Requests\Provider\ToggleEmployeeActiveRequest;
use App\Http\Requests\Provider\UpdateEmployeeRequest;
use App\Http\Resources\Provider\ProviderEmployeeResource;
use App\Http\Helpers\ApiResponse;
use App\Services\ProviderEmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Provider')]
#[Subgroup('Employees', 'Provider employees CRUD')]
class ProviderEmployeeController extends Controller
{
    public function __construct(
        private ProviderEmployeeService $employeeService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'search' => $request->input('search'),
                'branch_uuid' => $request->input('branch_uuid'),
                'active' => $request->has('active') ? filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN) : null,
                'blocked' => $request->has('blocked') ? filter_var($request->input('blocked'), FILTER_VALIDATE_BOOLEAN) : null,
            ];

            $perPage = (int) $request->input('per_page', 15);
            $paginated = $this->employeeService->index($filters, $perPage);

            return ApiResponse::success(
                ProviderEmployeeResource::collection($paginated->items()),
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

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $logo = $request->file('logo');
            $employee = $this->employeeService->store($data, $logo);

            return ApiResponse::success(
                new ProviderEmployeeResource($employee->load('branch')),
                'api.employees.created',
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
            $employee = $this->employeeService->show($uuid);
            return ApiResponse::success(
                new ProviderEmployeeResource($employee->load('branch'))
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.employees.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateEmployeeRequest $request, string $uuid): JsonResponse
    {
        try {
            $data = $request->validated();
            $logo = $request->file('logo');
            $employee = $this->employeeService->update($uuid, $data, $logo);

            return ApiResponse::success(
                new ProviderEmployeeResource($employee->load('branch')),
                'api.employees.updated'
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.employees.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function destroy(string $uuid): JsonResponse
    {
        try {
            $this->employeeService->destroy($uuid);
            return ApiResponse::success(null, 'api.employees.deleted');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.employees.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function toggleActive(ToggleEmployeeActiveRequest $request, string $uuid): JsonResponse
    {
        try {
            $employee = $this->employeeService->toggleActive($uuid, $request->validated()['active']);
            return ApiResponse::success(
                new ProviderEmployeeResource($employee->load('branch')),
                'api.employees.updated'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.employees.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function block(BlockEmployeeRequest $request, string $uuid): JsonResponse
    {
        try {
            $employee = $this->employeeService->block($uuid, $request->validated()['blocked']);
            return ApiResponse::success(
                new ProviderEmployeeResource($employee->load('branch')),
                'api.employees.updated'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.employees.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
