<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\ShiftDate;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookingAvailabilityService
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    /**
     * Return available calendar dates (YYYY-MM-DD strings) for a given employee
     * within a specific month where the employee has active shift dates.
     */
    public function getAvailableDates(array $params): array
    {
        $employee = $params['employee'];
        $branchId = $params['branch_id'];
        $month    = $params['month']; // format: YYYY-MM

        [$year, $monthNum] = explode('-', $month);
        $start = Carbon::createFromDate($year, $monthNum, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $shiftDates = ShiftDate::whereHas('shift', fn($q) => $q->where('branch_id', $branchId))
            ->whereHas('employees', fn($q) => $q->where('employees.id', $employee->id))
            ->where('active', true)
            ->whereBetween('shift_date', [$start->toDateString(), $end->toDateString()])
            ->whereNull('deleted_at')
            ->pluck('shift_date')
            ->map(fn($d) => $d instanceof Carbon ? $d->toDateString() : (string) $d)
            ->unique()
            ->values()
            ->toArray();

        return $shiftDates;
    }

    /**
     * Return available time slots for a given employee on a specific date.
     * A slot is available if it falls within the shift hours, outside break, and not already booked.
     */
    public function getAvailableSlots(array $params): array
    {
        $employee        = $params['employee'];
        $branchId        = $params['branch_id'];
        $date            = $params['date'];
        $durationMinutes = $params['duration_minutes'];

        $shiftDate = $this->resolveEmployeeShiftForDate($employee->id, $branchId, $date);

        if (! $shiftDate) {
            return [];
        }

        $slots = $this->generateSlots($shiftDate, $durationMinutes);
        return $this->excludeBookedSlots($slots, $employee->id, $date);
    }

    public function resolveEmployeeShiftForDate(int $employeeId, int $branchId, string $date): ?ShiftDate
    {
        return ShiftDate::whereHas('shift', fn($q) => $q->where('branch_id', $branchId))
            ->whereHas('employees', fn($q) => $q->where('employees.id', $employeeId))
            ->where('shift_date', $date)
            ->where('active', true)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * Generate all consecutive fixed-duration slots within shift working hours, excluding break time.
     *
     * @return array  [{start_time: string, end_time: string}]
     */
    public function generateSlots(ShiftDate $shiftDate, int $durationMinutes): array
    {
        $shiftStart = Carbon::parse($shiftDate->start_time);
        $shiftEnd   = Carbon::parse($shiftDate->end_time);

        $breakStart = $shiftDate->break_start ? Carbon::parse($shiftDate->break_start) : null;
        $breakEnd   = $shiftDate->break_end   ? Carbon::parse($shiftDate->break_end)   : null;

        $slots = [];
        $cursor = $shiftStart->copy();

        while ($cursor->copy()->addMinutes($durationMinutes)->lte($shiftEnd)) {
            $slotStart = $cursor->copy();
            $slotEnd   = $cursor->copy()->addMinutes($durationMinutes);

            // Skip slot if it overlaps with break
            $overlapsBreak = $breakStart && $breakEnd
                && $slotStart->lt($breakEnd)
                && $slotEnd->gt($breakStart);

            if (! $overlapsBreak) {
                $slots[] = [
                    'start_time' => $slotStart->format('H:i:s'),
                    'end_time'   => $slotEnd->format('H:i:s'),
                ];
            }

            $cursor->addMinutes($durationMinutes);
        }

        return $slots;
    }

    /**
     * Filter out slots that conflict with existing blocking bookings for the employee on that date.
     */
    public function excludeBookedSlots(array $slots, int $employeeId, string $date): array
    {
        $existingBookings = Booking::where('employee_id', $employeeId)
            ->where('booking_date', $date)
            ->whereIn('status', Booking::BLOCKING_STATUSES)
            ->get(['start_time', 'end_time']);

        if ($existingBookings->isEmpty()) {
            return $slots;
        }

        return array_values(array_filter($slots, function ($slot) use ($existingBookings) {
            foreach ($existingBookings as $booking) {
                // Slot conflicts if it overlaps with a booked interval
                if ($slot['start_time'] < $booking->end_time && $slot['end_time'] > $booking->start_time) {
                    return false;
                }
            }
            return true;
        }));
    }
}
