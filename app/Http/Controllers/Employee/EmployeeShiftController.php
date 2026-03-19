<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Resources\Employee\EmployeeShiftResource;
use App\Services\EmployeeShiftService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Employee')]
#[Subgroup('Shifts', 'View own assigned shift dates')]
class EmployeeShiftController extends Controller
{
    public function __construct(private EmployeeShiftService $service) {}

    /**
     * List assigned shift dates for the authenticated employee.
     *
     * Supports filters: date (Y-m-d), active.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = array_filter([
                'date'   => $request->input('date'),
                'active' => $request->has('active')
                    ? filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN)
                    : null,
            ], fn($v) => $v !== null);

            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->service->index($filters, $perPage);

            return ApiResponse::success(
                EmployeeShiftResource::collection($paginated->items()),
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
     * Show a single assigned shift date.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            return ApiResponse::success(new EmployeeShiftResource($this->service->show($uuid)));
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.shifts.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
