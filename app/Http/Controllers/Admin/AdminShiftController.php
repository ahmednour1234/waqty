<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Resources\Admin\AdminShiftResource;
use App\Http\Resources\Admin\AdminShiftTemplateResource;
use App\Services\AdminShiftService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Admin')]
#[Subgroup('Shifts', 'Admin view of all shifts and shift templates')]
class AdminShiftController extends Controller
{
    public function __construct(private AdminShiftService $service) {}

    /**
     * List all shifts (admin view).
     *
     * Supports filters: provider_uuid, branch_uuid, employee_uuid, date, active, shift_template_uuid.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = array_filter([
                'provider_uuid'       => $request->input('provider_uuid'),
                'branch_uuid'         => $request->input('branch_uuid'),
                'employee_uuid'       => $request->input('employee_uuid'),
                'date'                => $request->input('date'),
                'shift_template_uuid' => $request->input('shift_template_uuid'),
                'active'              => $request->has('active')
                    ? filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN)
                    : null,
            ], fn($v) => $v !== null);

            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->service->indexShifts($filters, $perPage);

            return ApiResponse::success(
                AdminShiftResource::collection($paginated->items()),
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
     * Show a single shift (admin view).
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            return ApiResponse::success(new AdminShiftResource($this->service->showShift($uuid)));
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.shifts.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * List all shift templates (admin view).
     *
     * Supports filters: provider_uuid, active.
     */
    public function indexTemplates(Request $request): JsonResponse
    {
        try {
            $filters = array_filter([
                'provider_uuid' => $request->input('provider_uuid'),
                'active'        => $request->has('active')
                    ? filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN)
                    : null,
                'search'        => $request->input('search'),
            ], fn($v) => $v !== null);

            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->service->indexTemplates($filters, $perPage);

            return ApiResponse::success(
                AdminShiftTemplateResource::collection($paginated->items()),
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
     * Show a single shift template (admin view).
     */
    public function showTemplate(string $uuid): JsonResponse
    {
        try {
            return ApiResponse::success(new AdminShiftTemplateResource($this->service->showTemplate($uuid)));
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.shift_templates.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
