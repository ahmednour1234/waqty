<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Employee\CheckInRequest;
use App\Http\Requests\Employee\CheckOutRequest;
use App\Http\Resources\Employee\EmployeeAttendanceResource;
use App\Services\EmployeeAttendanceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Employee')]
#[Subgroup('Attendance', 'Check in and out, and view personal attendance history')]
#[Header('Authorization', 'Bearer {token}')]
class EmployeeAttendanceController extends Controller
{
    public function __construct(private EmployeeAttendanceService $service) {}

    /**
     * Check in.
     *
     * Record a check-in for the authenticated employee. Optionally link the check-in
     * to a scheduled shift date. The employee must be assigned to the shift date if
     * a shift_date_uuid is provided.
     */
    #[BodyParam('shift_date_uuid', 'string', 'UUID of the assigned shift date to link check-in to.', required: false, example: '01hwz3k8m5n2q4r6s7t9v0w1xy')]
    #[BodyParam('notes', 'string', 'Optional check-in note.', required: false, example: 'Arrived on time.')]
    #[Response(['success' => true, 'data' => ['uuid' => '01hwz3k8m5n2q4r6s7t9v0w1xz', 'check_in_at' => '2026-03-24T08:00:00+00:00', 'check_out_at' => null, 'working_minutes' => null, 'status' => 'checked_in', 'notes' => null, 'shift_date' => null, 'created_at' => '2026-03-24T08:00:00+00:00']], 201)]
    #[Response(['success' => false, 'message' => 'api.attendance.already_checked_in'], 422, description: 'Employee already has an open check-in.')]
    #[Response(['success' => false, 'message' => 'api.attendance.shift_date_not_assigned'], 422, description: 'The shift date UUID provided is not assigned to this employee.')]
    public function checkIn(CheckInRequest $request): JsonResponse
    {
        try {
            $attendance = $this->service->checkIn($request->validated());
            return ApiResponse::success(new EmployeeAttendanceResource($attendance), 'api.attendance.checked_in', 201);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Check out.
     *
     * Record a check-out for the authenticated employee. Automatically computes
     * working_minutes from check_in_at to now.
     */
    #[BodyParam('notes', 'string', 'Optional check-out note.', required: false, example: 'Leaving after completing all tasks.')]
    #[Response(['success' => true, 'data' => ['uuid' => '01hwz3k8m5n2q4r6s7t9v0w1xz', 'check_in_at' => '2026-03-24T08:00:00+00:00', 'check_out_at' => '2026-03-24T17:00:00+00:00', 'working_minutes' => 540, 'status' => 'checked_out', 'notes' => null, 'shift_date' => null, 'created_at' => '2026-03-24T08:00:00+00:00']], 200)]
    #[Response(['success' => false, 'message' => 'api.attendance.no_active_check_in'], 404, description: 'No open check-in found for this employee.')]
    public function checkOut(CheckOutRequest $request): JsonResponse
    {
        try {
            $attendance = $this->service->checkOut($request->validated());
            return ApiResponse::success(new EmployeeAttendanceResource($attendance), 'api.attendance.checked_out');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * List attendance history.
     *
     * Returns the authenticated employee's paginated attendance records.
     */
    #[QueryParam('date_from', 'string', 'Filter from date (Y-m-d).', required: false, example: '2026-03-01')]
    #[QueryParam('date_to', 'string', 'Filter to date (Y-m-d).', required: false, example: '2026-03-31')]
    #[QueryParam('shift_date_uuid', 'string', 'Filter by a specific shift date UUID.', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page.', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = array_filter([
                'date_from'       => $request->input('date_from'),
                'date_to'         => $request->input('date_to'),
                'shift_date_uuid' => $request->input('shift_date_uuid'),
            ], fn($v) => $v !== null);

            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->service->index($filters, $perPage);

            return ApiResponse::success(
                EmployeeAttendanceResource::collection($paginated->items()),
                null,
                200,
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
}
