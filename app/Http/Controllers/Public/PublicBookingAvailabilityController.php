<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Public\BookingAvailableDatesRequest;
use App\Http\Requests\Public\BookingAvailableSlotsRequest;
use App\Models\Employee;
use App\Models\ProviderBranch;
use App\Models\Service;
use App\Services\BookingAvailabilityService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Public')]
#[Subgroup('Booking Availability', 'Check available dates and time slots')]
class PublicBookingAvailabilityController extends Controller
{
    public function __construct(
        private BookingAvailabilityService $availabilityService
    ) {}

    /**
     * Get available booking dates for a given employee/service/branch in a month.
     */
    public function availableDates(BookingAvailableDatesRequest $request): JsonResponse
    {
        try {
            $branch   = ProviderBranch::whereUuid($request->branch_uuid)->firstOrFail();
            $employee = Employee::whereUuid($request->employee_uuid)->firstOrFail();
            $service  = Service::whereUuid($request->service_uuid)
                ->with(['providers' => fn($q) => $q->where('providers.id', $branch->provider_id)])
                ->firstOrFail();

            $pivot           = $service->providers->first()?->pivot;
            $durationMinutes = $pivot?->estimated_duration_minutes;

            if (! $durationMinutes) {
                return ApiResponse::error(__('api.bookings.no_duration_set'), 422);
            }

            $dates = $this->availabilityService->getAvailableDates([
                'employee'  => $employee,
                'branch_id' => $branch->id,
                'month'     => $request->month,
            ]);

            return ApiResponse::success(['dates' => $dates]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error(__('api.general.not_found'), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Get available time slots for a given employee/service/branch on a specific date.
     */
    public function availableSlots(BookingAvailableSlotsRequest $request): JsonResponse
    {
        try {
            $branch   = ProviderBranch::whereUuid($request->branch_uuid)->firstOrFail();
            $employee = Employee::whereUuid($request->employee_uuid)->firstOrFail();
            $service  = Service::whereUuid($request->service_uuid)
                ->with(['providers' => fn($q) => $q->where('providers.id', $branch->provider_id)])
                ->firstOrFail();

            $pivot           = $service->providers->first()?->pivot;
            $durationMinutes = $pivot?->estimated_duration_minutes;

            if (! $durationMinutes) {
                return ApiResponse::error(__('api.bookings.no_duration_set'), 422);
            }

            $slots = $this->availabilityService->getAvailableSlots([
                'employee'         => $employee,
                'branch_id'        => $branch->id,
                'date'             => $request->date,
                'duration_minutes' => $durationMinutes,
            ]);

            return ApiResponse::success(['slots' => $slots]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error(__('api.general.not_found'), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
