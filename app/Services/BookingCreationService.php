<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\ProviderBranch;
use App\Models\Service;
use App\Models\User;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Support\Facades\DB;

class BookingCreationService
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository,
        private PriceResolverService $priceResolver,
        private BookingAvailabilityService $availabilityService
    ) {}

    /**
     * Create a new booking inside a DB transaction.
     * Validates employee eligibility, duration, pricing, and slot availability.
     *
     * @throws \InvalidArgumentException on business rule violations
     */
    public function create(User $user, array $data): Booking
    {
        // Resolve models by UUID
        $branch   = ProviderBranch::whereUuid($data['branch_uuid'])->firstOrFail();
        $employee = Employee::whereUuid($data['employee_uuid'])->firstOrFail();
        $service  = Service::whereUuid($data['service_uuid'])
            ->with(['providers' => fn($q) => $q->where('providers.id', $branch->provider_id)])
            ->firstOrFail();

        // Check employee belongs to the same provider as the branch
        if ($employee->provider_id !== $branch->provider_id) {
            throw new \InvalidArgumentException(__('api.bookings.employee_not_available'));
        }

        // Check service is attached to this provider
        $pivot = $service->providers->first()?->pivot;
        if (! $pivot || $pivot->deleted_at !== null) {
            throw new \InvalidArgumentException(__('api.bookings.service_not_available'));
        }

        // Resolve duration from pivot
        $durationMinutes = $pivot->estimated_duration_minutes;
        if (! $durationMinutes) {
            throw new \InvalidArgumentException(__('api.bookings.no_duration_set'));
        }

        // Resolve price
        $pricing = $this->priceResolver->getPrice($service->id, $employee->id, $branch->id);
        if (! $pricing) {
            throw new \InvalidArgumentException(__('api.service_prices.no_price_found'));
        }

        // Build snapshots
        $serviceSnapshot  = $this->buildServiceSnapshot($service, $durationMinutes);
        $employeeSnapshot = $this->buildEmployeeSnapshot($employee);
        $branchSnapshot   = $this->buildBranchSnapshot($branch);
        $providerSnapshot = $this->buildProviderSnapshot($branch->provider_id);

        // Compute end_time
        $startTime = $data['start_time'];
        $endTime   = \Carbon\Carbon::parse($startTime)->addMinutes($durationMinutes)->format('H:i:s');

        return DB::transaction(function () use (
            $user, $branch, $employee, $service, $data, $pricing,
            $durationMinutes, $startTime, $endTime,
            $serviceSnapshot, $employeeSnapshot, $branchSnapshot, $providerSnapshot
        ) {
            // Final conflict check inside transaction to prevent race conditions
            if ($this->bookingRepository->hasConflict($employee->id, $data['booking_date'], $startTime, $endTime)) {
                throw new \InvalidArgumentException(__('api.bookings.slot_not_available'));
            }

            return $this->bookingRepository->create([
                'user_id'           => $user->id,
                'provider_id'       => $branch->provider_id,
                'branch_id'         => $branch->id,
                'employee_id'       => $employee->id,
                'service_id'        => $service->id,
                'booking_date'      => $data['booking_date'],
                'start_time'        => $startTime,
                'end_time'          => $endTime,
                'price'             => $pricing['final_price'],
                'currency'          => $pricing['currency'] ?? 'SAR',
                'status'            => Booking::STATUS_PENDING,
                'payment_status'    => Booking::PAYMENT_STATUS_UNPAID,
                'notes'             => $data['notes'] ?? null,
                'service_snapshot'  => $serviceSnapshot,
                'employee_snapshot' => $employeeSnapshot,
                'branch_snapshot'   => $branchSnapshot,
                'provider_snapshot' => $providerSnapshot,
            ]);
        });
    }

    private function buildServiceSnapshot(Service $service, int $durationMinutes): array
    {
        return [
            'uuid'                       => $service->uuid,
            'name'                       => $service->name,
            'estimated_duration_minutes' => $durationMinutes,
        ];
    }

    private function buildEmployeeSnapshot(Employee $employee): array
    {
        return [
            'uuid'  => $employee->uuid,
            'name'  => $employee->name,
            'email' => $employee->email,
        ];
    }

    private function buildBranchSnapshot(ProviderBranch $branch): array
    {
        return [
            'uuid' => $branch->uuid,
            'name' => $branch->name,
        ];
    }

    private function buildProviderSnapshot(int $providerId): array
    {
        $provider = \App\Models\Provider::find($providerId);
        return [
            'uuid' => $provider?->uuid,
            'name' => $provider?->name,
        ];
    }
}
