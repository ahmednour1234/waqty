<?php

namespace App\Repositories;

use App\Models\PricingGroupEmployee;
use App\Repositories\Contracts\PricingGroupEmployeeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class PricingGroupEmployeeRepository implements PricingGroupEmployeeRepositoryInterface
{
    public function getByGroup(int $groupId): Collection
    {
        return PricingGroupEmployee::where('pricing_group_id', $groupId)
            ->with('employee')
            ->get();
    }

    public function syncEmployees(int $groupId, array $employeeIds): void
    {
        // Remove all current members not in the new list
        PricingGroupEmployee::where('pricing_group_id', $groupId)
            ->whereNotIn('employee_id', $employeeIds)
            ->delete();

        // Add missing ones
        $existing = PricingGroupEmployee::where('pricing_group_id', $groupId)
            ->whereIn('employee_id', $employeeIds)
            ->pluck('employee_id')
            ->toArray();

        $toInsert = array_diff($employeeIds, $existing);

        foreach ($toInsert as $employeeId) {
            PricingGroupEmployee::create([
                'uuid'             => (string) Str::ulid(),
                'pricing_group_id' => $groupId,
                'employee_id'      => $employeeId,
            ]);
        }
    }

    public function addEmployees(int $groupId, array $employeeIds): void
    {
        $existing = PricingGroupEmployee::where('pricing_group_id', $groupId)
            ->whereIn('employee_id', $employeeIds)
            ->pluck('employee_id')
            ->toArray();

        $toInsert = array_diff($employeeIds, $existing);

        foreach ($toInsert as $employeeId) {
            PricingGroupEmployee::create([
                'uuid'             => (string) Str::ulid(),
                'pricing_group_id' => $groupId,
                'employee_id'      => $employeeId,
            ]);
        }
    }

    public function removeEmployees(int $groupId, array $employeeIds): void
    {
        PricingGroupEmployee::where('pricing_group_id', $groupId)
            ->whereIn('employee_id', $employeeIds)
            ->delete();
    }

    public function getGroupIdsForEmployee(int $employeeId): array
    {
        return PricingGroupEmployee::where('employee_id', $employeeId)
            ->pluck('pricing_group_id')
            ->toArray();
    }
}
