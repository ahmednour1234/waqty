<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PricingGroup;
use App\Repositories\Contracts\PricingGroupEmployeeRepositoryInterface;
use App\Repositories\Contracts\PricingGroupRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProviderPricingGroupService
{
    public function __construct(
        private PricingGroupRepositoryInterface         $groupRepository,
        private PricingGroupEmployeeRepositoryInterface $groupEmployeeRepository,
    ) {}

    public function index(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $provider = $this->authenticatedProvider();
        return $this->groupRepository->paginateProvider($provider->id, $filters, $perPage);
    }

    public function show(string $uuid): PricingGroup
    {
        $provider = $this->authenticatedProvider();
        $group = $this->groupRepository->findByUuidAndProvider($uuid, $provider->id);

        if (!$group) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('api.pricing_groups.not_found');
        }

        return $group;
    }

    public function store(array $data): PricingGroup
    {
        $provider = $this->authenticatedProvider();

        return DB::transaction(function () use ($data, $provider) {
            $group = $this->groupRepository->create([
                'provider_id' => $provider->id,
                'name'        => $data['name'],
                'active'      => $data['active'] ?? true,
            ]);

            if (!empty($data['employee_uuids'])) {
                $employeeIds = $this->resolveEmployeeIds($data['employee_uuids'], $provider->id);
                $this->groupEmployeeRepository->syncEmployees($group->id, $employeeIds);
            }

            return $group->load('employees');
        });
    }

    public function update(string $uuid, array $data): PricingGroup
    {
        $provider = $this->authenticatedProvider();
        $group = $this->groupRepository->findByUuidAndProvider($uuid, $provider->id);

        if (!$group) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('api.pricing_groups.not_found');
        }

        return DB::transaction(function () use ($group, $data, $provider) {
            $updateData = array_filter([
                'name'   => $data['name'] ?? null,
                'active' => $data['active'] ?? null,
            ], fn ($v) => $v !== null);

            if (!empty($updateData)) {
                $this->groupRepository->update($group, $updateData);
            }

            if (array_key_exists('employee_uuids', $data)) {
                $employeeIds = $data['employee_uuids']
                    ? $this->resolveEmployeeIds($data['employee_uuids'], $provider->id)
                    : [];
                $this->groupEmployeeRepository->syncEmployees($group->id, $employeeIds);
            }

            return $group->fresh(['employees']);
        });
    }

    public function destroy(string $uuid): void
    {
        $provider = $this->authenticatedProvider();
        $group = $this->groupRepository->findByUuidAndProvider($uuid, $provider->id);

        if (!$group) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('api.pricing_groups.not_found');
        }

        $this->groupRepository->softDelete($group);
    }

    public function toggleActive(string $uuid): PricingGroup
    {
        $provider = $this->authenticatedProvider();
        $group = $this->groupRepository->findByUuidAndProvider($uuid, $provider->id);

        if (!$group) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('api.pricing_groups.not_found');
        }

        return $this->groupRepository->toggleActive($group, !$group->active);
    }

    public function syncEmployees(string $uuid, array $employeeUuids): PricingGroup
    {
        $provider = $this->authenticatedProvider();
        $group = $this->findOwnedGroup($uuid, $provider->id);

        $employeeIds = $this->resolveEmployeeIds($employeeUuids, $provider->id);
        $this->groupEmployeeRepository->syncEmployees($group->id, $employeeIds);

        return $group->fresh(['employees']);
    }

    public function addEmployees(string $uuid, array $employeeUuids): PricingGroup
    {
        $provider = $this->authenticatedProvider();
        $group = $this->findOwnedGroup($uuid, $provider->id);

        $employeeIds = $this->resolveEmployeeIds($employeeUuids, $provider->id);
        $this->groupEmployeeRepository->addEmployees($group->id, $employeeIds);

        return $group->fresh(['employees']);
    }

    public function removeEmployees(string $uuid, array $employeeUuids): PricingGroup
    {
        $provider = $this->authenticatedProvider();
        $group = $this->findOwnedGroup($uuid, $provider->id);

        $employeeIds = $this->resolveEmployeeIds($employeeUuids, $provider->id);
        $this->groupEmployeeRepository->removeEmployees($group->id, $employeeIds);

        return $group->fresh(['employees']);
    }

    private function findOwnedGroup(string $uuid, int $providerId): PricingGroup
    {
        $group = $this->groupRepository->findByUuidAndProvider($uuid, $providerId);

        if (!$group) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('api.pricing_groups.not_found');
        }

        if ($group->trashed()) {
            throw new \InvalidArgumentException('api.pricing_groups.is_deleted');
        }

        return $group;
    }

    private function resolveEmployeeIds(array $uuids, int $providerId): array
    {
        $ids = [];
        foreach ($uuids as $uuid) {
            $employee = Employee::whereUuid($uuid)
                ->where('provider_id', $providerId)
                ->whereNull('deleted_at')
                ->first();

            if (!$employee) {
                throw new \InvalidArgumentException('api.employees.not_found');
            }

            $ids[] = $employee->id;
        }

        return $ids;
    }

    private function authenticatedProvider()
    {
        return Auth::guard('provider')->user();
    }
}
