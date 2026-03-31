<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Employee\EmployeeSetAvailabilityRequest;
use App\Http\Resources\Employee\EmployeeBookingResource;
use App\Http\Resources\Employee\EmployeeSelfResource;
use App\Services\EmployeeAvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Employee')]
#[Subgroup('Availability', 'Employee real-time availability management')]
class EmployeeAvailabilityController extends Controller
{
    public function __construct(
        private EmployeeAvailabilityService $availabilityService
    ) {
    }

    /**
     * Get the authenticated employee's current availability status.
     */
    public function show(): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();

            return ApiResponse::success([
                'availability_status'     => $employee->availability_status,
                'availability_updated_at' => $employee->availability_updated_at?->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Manually update availability status (available / break / off).
     */
    public function update(EmployeeSetAvailabilityRequest $request): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();
            $updated  = $this->availabilityService->setStatus($employee, $request->input('status'));

            return ApiResponse::success([
                'availability_status'     => $updated->availability_status,
                'availability_updated_at' => $updated->availability_updated_at?->toIso8601String(),
            ]);
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Start a session for a booking — sets employee to in_session and records session_started_at.
     */
    public function startSession(string $bookingUuid): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();
            $booking  = $this->availabilityService->startSession($employee, $bookingUuid);

            return ApiResponse::success(
                new EmployeeBookingResource($booking),
                null,
                200
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * End a session for a booking — records session_ended_at and reverts employee to available.
     */
    public function endSession(string $bookingUuid): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();
            $booking  = $this->availabilityService->endSession($employee, $bookingUuid);

            return ApiResponse::success(
                new EmployeeBookingResource($booking),
                null,
                200
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
