<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\ProviderBranch;
use App\Models\Service;
use App\Models\User;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuickSaleService
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository,
        private PriceResolverService $priceResolver,
    ) {}

    /**
     * Create a completed sale record with minimal required data.
     * - service_uuid is the only required field.
     * - Defaults to main branch when branch_uuid is omitted.
     * - employee is optional (null = any / unspecified).
     * - user can be an existing user (user_uuid) or a walk-in (user_name/user_phone).
     * - Price falls back to the resolved service price; pass 'price' to override.
     * - A payment record is created automatically when payment_method is provided.
     *
     * @throws \InvalidArgumentException
     */
    public function create(Provider $provider, array $data): Booking
    {
        // 1. Resolve branch
        $branch = $this->resolveBranch($provider, $data['branch_uuid'] ?? null);

        // 2. Resolve employee (optional)
        $employee = null;
        if (! empty($data['employee_uuid'])) {
            $employee = Employee::whereUuid($data['employee_uuid'])
                ->where('provider_id', $provider->id)
                ->firstOrFail();
        }

        // 3. Resolve service (must be attached to this provider)
        $service = Service::whereUuid($data['service_uuid'])
            ->with(['providers' => fn ($q) => $q->where('providers.id', $provider->id)])
            ->firstOrFail();

        $pivot = $service->providers->first()?->pivot;
        if (! $pivot || $pivot->deleted_at !== null) {
            throw new \InvalidArgumentException(__('api.bookings.service_not_available'));
        }

        // 4. Resolve price
        $price = $data['price'] ?? null;
        if ($price === null) {
            $pricing = $this->priceResolver->getPrice(
                $service->id,
                $employee?->id,
                $branch->id,
            );
            $price = $pricing ? (float) $pricing['final_price'] : 0.00;
        }

        // 5. Resolve user (optional — existing or walk-in)
        $userId    = null;
        $userName  = $data['user_name'] ?? null;
        $userPhone = $data['user_phone'] ?? null;

        if (! empty($data['user_uuid'])) {
            $user      = User::whereUuid($data['user_uuid'])->whereNull('deleted_at')->firstOrFail();
            $userId    = $user->id;
            $userName  = $userName ?? $user->name;
            $userPhone = $userPhone ?? $user->phone;
        }

        // 6. Resolve date/time
        $now         = Carbon::now();
        $bookingDate = $data['booking_date'] ?? $now->toDateString();
        $startTime   = $data['booking_time']  ?? $now->format('H:i');
        $duration    = $pivot->estimated_duration_minutes ?? 0;
        $endTime     = $duration > 0
            ? Carbon::parse($startTime)->addMinutes($duration)->format('H:i:s')
            : Carbon::parse($startTime)->format('H:i:s');

        // 7. Build snapshots
        $serviceSnapshot = [
            'uuid'                       => $service->uuid,
            'name'                       => $service->name,
            'estimated_duration_minutes' => $duration,
        ];

        $employeeSnapshot = $employee ? [
            'uuid'  => $employee->uuid,
            'name'  => $employee->name,
            'email' => $employee->email,
        ] : null;

        $branchSnapshot = [
            'uuid' => $branch->uuid,
            'name' => $branch->name,
        ];

        $providerModel    = Provider::find($provider->id);
        $providerSnapshot = [
            'uuid' => $providerModel?->uuid,
            'name' => $providerModel?->name,
        ];

        return DB::transaction(function () use (
            $provider, $branch, $employee, $service, $data,
            $userId, $userName, $userPhone,
            $price, $bookingDate, $startTime, $endTime,
            $serviceSnapshot, $employeeSnapshot, $branchSnapshot, $providerSnapshot
        ) {
            $booking = $this->bookingRepository->create([
                'user_id'           => $userId,
                'provider_id'       => $provider->id,
                'branch_id'         => $branch->id,
                'employee_id'       => $employee?->id,
                'service_id'        => $service->id,
                'booking_date'      => $bookingDate,
                'start_time'        => $startTime,
                'end_time'          => $endTime,
                'price'             => $price,
                'currency'          => 'SAR',
                'status'            => Booking::STATUS_COMPLETED,
                'payment_status'    => Booking::PAYMENT_STATUS_UNPAID,
                'notes'             => $data['notes'] ?? null,
                'user_name'         => $userName,
                'user_phone'        => $userPhone,
                'service_snapshot'  => $serviceSnapshot,
                'employee_snapshot' => $employeeSnapshot,
                'branch_snapshot'   => $branchSnapshot,
                'provider_snapshot' => $providerSnapshot,
            ]);

            // 8. Record payment if method provided
            if (! empty($data['payment_method'])) {
                $paymentAmount = isset($data['payment_amount']) ? (float) $data['payment_amount'] : $price;

                Payment::create([
                    'uuid'           => Str::ulid(),
                    'booking_id'     => $booking->id,
                    'payment_method' => $data['payment_method'],
                    'amount'         => $paymentAmount,
                    'status'         => Payment::STATUS_COMPLETED,
                    'notes'          => null,
                ]);

                $booking->update(['payment_status' => Booking::PAYMENT_STATUS_PAID]);
            }

            return $booking->fresh(['latestPayment', 'user']);
        });
    }

    private function resolveBranch(Provider $provider, ?string $branchUuid): ProviderBranch
    {
        if ($branchUuid) {
            return ProviderBranch::whereUuid($branchUuid)
                ->where('provider_id', $provider->id)
                ->firstOrFail();
        }

        // Default to main branch
        $main = ProviderBranch::where('provider_id', $provider->id)
            ->where('is_main', true)
            ->whereNull('deleted_at')
            ->first();

        if (! $main) {
            // Fall back to any active branch
            $main = ProviderBranch::where('provider_id', $provider->id)
                ->where('active', true)
                ->whereNull('deleted_at')
                ->first();
        }

        if (! $main) {
            throw new \InvalidArgumentException(__('api.branches.no_branch_found'));
        }

        return $main;
    }
}
