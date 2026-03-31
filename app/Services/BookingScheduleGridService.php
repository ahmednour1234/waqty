<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Provider;
use App\Models\ProviderBranch;
use App\Models\ShiftDate;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookingScheduleGridService
{
    /**
     * Slot interval in minutes used for the grid.
     */
    const SLOT_INTERVAL = 30;

    /**
     * Build a time-slot × employee grid for a provider on a given date.
     *
     * @return array{
     *   date: string,
     *   employees: array,
     *   slots: array,
     *   grid: array
     * }
     */
    public function gridForProvider(Provider $provider, string $date, ?string $branchUuid = null): array
    {
        $branchIds = $provider->branches()->where('active', true)->pluck('id');
        if ($branchUuid) {
            $branch = ProviderBranch::whereUuid($branchUuid)
                ->where('provider_id', $provider->id)
                ->first();
            $branchIds = $branch ? collect([$branch->id]) : collect();
        }

        return $this->buildGrid($date, $branchIds);
    }

    /**
     * Build a time-slot × employee grid for a single branch on a given date.
     */
    public function gridForBranch(ProviderBranch $branch, string $date): array
    {
        return $this->buildGrid($date, collect([$branch->id]));
    }

    // ──────────────────────────────────────────────
    // Internal helpers
    // ──────────────────────────────────────────────

    private function buildGrid(string $date, Collection $branchIds): array
    {
        if ($branchIds->isEmpty()) {
            return $this->emptyGrid($date);
        }

        // 1. Find all shift dates for those branches on this day
        $shiftDates = ShiftDate::whereHas('shift', fn($q) => $q->whereIn('branch_id', $branchIds))
            ->where('shift_date', $date)
            ->where('active', true)
            ->whereNull('deleted_at')
            ->with('employees')
            ->get();

        if ($shiftDates->isEmpty()) {
            return $this->emptyGrid($date);
        }

        // 2. Gather unique working employees with their shift window
        /** @var array<int, array{employee: Employee, start: Carbon, end: Carbon}> $employeeShifts */
        $employeeShifts = [];
        foreach ($shiftDates as $sd) {
            foreach ($sd->employees as $emp) {
                // If employee already found, extend their window to cover this shift date
                if (isset($employeeShifts[$emp->id])) {
                    $existing = $employeeShifts[$emp->id];
                    if (Carbon::parse($sd->start_time)->lt($existing['start'])) {
                        $employeeShifts[$emp->id]['start'] = Carbon::parse($sd->start_time);
                    }
                    if (Carbon::parse($sd->end_time)->gt($existing['end'])) {
                        $employeeShifts[$emp->id]['end'] = Carbon::parse($sd->end_time);
                    }
                } else {
                    $employeeShifts[$emp->id] = [
                        'employee' => $emp,
                        'start'    => Carbon::parse($sd->start_time),
                        'end'      => Carbon::parse($sd->end_time),
                    ];
                }
            }
        }

        if (empty($employeeShifts)) {
            return $this->emptyGrid($date);
        }

        // 3. Determine overall day window (earliest start → latest end)
        $dayStart = collect($employeeShifts)->min(fn($e) => $e['start']->timestamp);
        $dayEnd   = collect($employeeShifts)->max(fn($e) => $e['end']->timestamp);

        $dayStart = Carbon::createFromTimestamp($dayStart)->startOfMinute();
        $dayEnd   = Carbon::createFromTimestamp($dayEnd)->startOfMinute();

        // Snap to nearest slot boundary
        $dayStart = $this->snapDown($dayStart);
        $dayEnd   = $this->snapUp($dayEnd);

        // 4. Generate slot labels for the day
        $slots = [];
        $cursor = $dayStart->copy();
        while ($cursor->lt($dayEnd)) {
            $slots[] = $cursor->format('H:i');
            $cursor->addMinutes(self::SLOT_INTERVAL);
        }

        // 5. Load all bookings for those employees on this date
        $employeeIds = array_keys($employeeShifts);
        $bookings    = Booking::whereIn('employee_id', $employeeIds)
            ->where('booking_date', $date)
            ->whereNotIn('status', [Booking::STATUS_CANCELLED])
            ->with(['user', 'service'])
            ->get();

        // Index bookings by employee_id
        $bookingsByEmployee = $bookings->groupBy('employee_id');

        // 6. Build the grid structure
        $employeeList = [];
        $grid         = [];  // grid[$slotTime][$employeeUuid] = booking cell or null

        foreach ($slots as $slot) {
            $grid[$slot] = [];
        }

        foreach ($employeeShifts as $empId => $empData) {
            $emp = $empData['employee'];

            $employeeList[] = [
                'uuid'               => $emp->uuid,
                'name'               => $emp->name,
                'job_title'          => $emp->job_title ?? null,
                'availability_status'=> $emp->availability_status ?? null,
                'shift_start'        => $empData['start']->format('H:i'),
                'shift_end'          => $empData['end']->format('H:i'),
            ];

            $empBookings = $bookingsByEmployee->get($empId, collect());

            foreach ($slots as $slotTime) {
                $slotStart = Carbon::createFromFormat('Y-m-d H:i', "$date $slotTime");
                $slotEnd   = $slotStart->copy()->addMinutes(self::SLOT_INTERVAL);

                // Check if employee is working this slot
                $isWorking = $slotStart->gte($empData['start']) && $slotEnd->lte($empData['end']);

                if (! $isWorking) {
                    $grid[$slotTime][$emp->uuid] = ['type' => 'off'];
                    continue;
                }

                // Find any booking whose time window covers this slot
                $booking = $empBookings->first(function ($b) use ($slotStart, $slotEnd) {
                    $bStart = Carbon::parse($b->start_time);
                    $bEnd   = Carbon::parse($b->end_time);
                    // booking covers the slot if it starts at or before the slot and ends at or after slot end
                    return $bStart->lte($slotStart) && $bEnd->gte($slotEnd);
                });

                if ($booking) {
                    $bStart = Carbon::parse($booking->start_time);
                    // Only attach full booking info on the first slot it occupies
                    $isFirstSlot = $this->snapDown($bStart)->format('H:i') === $slotTime;

                    $grid[$slotTime][$emp->uuid] = [
                        'type'       => 'booking',
                        'is_first'   => $isFirstSlot,
                        'span_slots' => (int) ceil(Carbon::parse($booking->start_time)->diffInMinutes(Carbon::parse($booking->end_time)) / self::SLOT_INTERVAL),
                        'booking'    => $isFirstSlot ? [
                            'uuid'         => $booking->uuid,
                            'status'       => $booking->status,
                            'payment_status'=> $booking->payment_status,
                            'start_time'   => $booking->start_time,
                            'end_time'     => $booking->end_time,
                            'price'        => $booking->price,
                            'notes'        => $booking->notes,
                            'service'      => $booking->service_snapshot,
                            'customer'     => $booking->user ? [
                                'uuid'  => $booking->user->uuid,
                                'name'  => $booking->user->name,
                                'phone' => $booking->user->phone,
                            ] : null,
                        ] : null,
                    ];
                } else {
                    $grid[$slotTime][$emp->uuid] = ['type' => 'available'];
                }
            }
        }

        // 7. Convert grid to array-of-rows format
        $rows = [];
        foreach ($slots as $slotTime) {
            $rows[] = [
                'time'  => $slotTime,
                'cells' => $grid[$slotTime],
            ];
        }

        return [
            'date'      => $date,
            'interval'  => self::SLOT_INTERVAL,
            'employees' => $employeeList,
            'slots'     => $slots,
            'rows'      => $rows,
        ];
    }

    private function emptyGrid(string $date): array
    {
        return [
            'date'      => $date,
            'interval'  => self::SLOT_INTERVAL,
            'employees' => [],
            'slots'     => [],
            'rows'      => [],
        ];
    }

    /** Snap time DOWN to nearest slot boundary */
    private function snapDown(Carbon $time): Carbon
    {
        $minutes = (int) floor($time->minute / self::SLOT_INTERVAL) * self::SLOT_INTERVAL;
        return $time->copy()->setMinute($minutes)->setSecond(0);
    }

    /** Snap time UP to nearest slot boundary */
    private function snapUp(Carbon $time): Carbon
    {
        if ($time->second > 0 || $time->minute % self::SLOT_INTERVAL !== 0) {
            $minutes = (int) ceil($time->minute / self::SLOT_INTERVAL) * self::SLOT_INTERVAL;
            if ($minutes >= 60) {
                return $time->copy()->addHour()->setMinute(0)->setSecond(0);
            }
            return $time->copy()->setMinute($minutes)->setSecond(0);
        }
        return $time->copy()->setSecond(0);
    }
}
