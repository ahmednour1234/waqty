<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Provider\StoreShiftRequest;
use App\Http\Requests\Provider\UpdateShiftRequest;
use App\Http\Resources\Provider\ProviderShiftResource;
use App\Services\ProviderShiftService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Provider')]
#[Subgroup('Shifts', 'Create and manage scheduled shifts')]
class ProviderShiftController extends Controller
{
    public function __construct(private ProviderShiftService $service) {}

    /**
     * List shifts.
     *
     * Supports filters: branch_uuid, employee_uuid, date, active, shift_template_uuid.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = array_filter([
                'branch_uuid'         => $request->input('branch_uuid'),
                'employee_uuid'       => $request->input('employee_uuid'),
                'date'                => $request->input('date'),
                'shift_template_uuid' => $request->input('shift_template_uuid'),
                'active'              => $request->has('active')
                    ? filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN)
                    : null,
            ], fn($v) => $v !== null);

            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->service->index($filters, $perPage);

            return ApiResponse::success(
                ProviderShiftResource::collection($paginated->items()),
                null, 200,
                ['pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'per_page'     => $paginated->perPage(),
                    'total'        => $paginated->total(),
                    'last_page'    => $paginated->lastPage(),
                ]]
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Create a shift (bulk date generation + employee assignment).
     */
    public function store(StoreShiftRequest $request): JsonResponse
    {
        try {
            $shift = $this->service->store($request->validated());
            return ApiResponse::success(new ProviderShiftResource($shift), 'api.shifts.created', 201);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Show a shift with all its generated dates and assigned employees.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            return ApiResponse::success(new ProviderShiftResource($this->service->show($uuid)));
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.shifts.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Update shift metadata (title, notes, active, branch).
     */
    public function update(UpdateShiftRequest $request, string $uuid): JsonResponse
    {
        try {
            $shift = $this->service->update($uuid, $request->validated());
            return ApiResponse::success(new ProviderShiftResource($shift), 'api.shifts.updated');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Soft-delete a shift and all its shift dates.
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $this->service->destroy($uuid);
            return ApiResponse::success(null, 'api.shifts.deleted');
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.shifts.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
