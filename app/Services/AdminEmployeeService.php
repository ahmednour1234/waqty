<?php

namespace App\Services;

use App\Repositories\Contracts\EmployeeRepositoryInterface;

class AdminEmployeeService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {
    }

    public function index(array $filters = [], int $perPage = 15)
    {
        return $this->employeeRepository->paginateAdmin($filters, $perPage);
    }

    public function show(string $uuid)
    {
        $employee = $this->employeeRepository->findByUuid($uuid);

        if (!$employee) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Employee not found');
        }

        return $employee;
    }

    public function updateStatus(string $uuid, array $data)
    {
        $employee = $this->employeeRepository->findByUuid($uuid);

        if (!$employee) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Employee not found');
        }

        $updateData = [];

        if (isset($data['active'])) {
            $updateData['active'] = $data['active'];
        }

        if (isset($data['blocked'])) {
            $updateData['blocked'] = $data['blocked'];
        }

        if (empty($updateData)) {
            return $employee;
        }

        return $this->employeeRepository->update($employee, $updateData);
    }

    public function destroy(string $uuid): bool
    {
        $employee = $this->employeeRepository->findByUuid($uuid);

        if (!$employee) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Employee not found');
        }

        return $this->employeeRepository->softDelete($employee);
    }

    public function restore(string $uuid)
    {
        $employee = \App\Models\Employee::onlyTrashed()->whereUuid($uuid)->first();

        if (!$employee) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Employee not found');
        }

        $employee->restore();
        return $employee->fresh();
    }
}
