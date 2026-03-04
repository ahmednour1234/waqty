<?php

namespace App\Repositories;

use App\Models\Employee;
use App\Models\Provider;
use App\Models\ProviderBranch;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    public function findByEmail(string $email): ?Employee
    {
        return Employee::where('email', $email)
            ->whereNull('deleted_at')
            ->first();
    }

    public function findByUuid(string $uuid): ?Employee
    {
        return Employee::whereUuid($uuid)
            ->whereNull('deleted_at')
            ->first();
    }

    public function paginateAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Employee::with(['provider', 'branch']);

        if (isset($filters['trashed']) && $filters['trashed'] === 'only') {
            $query->onlyTrashed();
        } elseif (isset($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        } else {
            $query->whereNull('deleted_at');
        }

        if (isset($filters['provider_uuid'])) {
            $provider = Provider::whereUuid($filters['provider_uuid'])->first();
            if ($provider) {
                $query->where('provider_id', $provider->id);
            } else {
                $query->where('provider_id', 0);
            }
        }

        if (isset($filters['branch_uuid'])) {
            $branch = ProviderBranch::whereUuid($filters['branch_uuid'])->first();
            if ($branch) {
                $query->where('branch_id', $branch->id);
            } else {
                $query->where('branch_id', 0);
            }
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (isset($filters['blocked'])) {
            $query->where('blocked', $filters['blocked']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function paginateProvider(int $providerId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Employee::with(['branch'])
            ->where('provider_id', $providerId)
            ->whereNull('deleted_at');

        if (isset($filters['branch_uuid'])) {
            $branch = ProviderBranch::whereUuid($filters['branch_uuid'])->first();
            if ($branch && $branch->provider_id === $providerId) {
                $query->where('branch_id', $branch->id);
            } else {
                $query->where('branch_id', 0);
            }
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (isset($filters['blocked'])) {
            $query->where('blocked', $filters['blocked']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Employee
    {
        return Employee::create($data);
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);
        return $employee->fresh();
    }

    public function softDelete(Employee $employee): bool
    {
        return $employee->delete();
    }

    public function toggleActive(Employee $employee, bool $active): Employee
    {
        $employee->update(['active' => $active]);
        return $employee->fresh();
    }

    public function setBlocked(Employee $employee, bool $blocked): Employee
    {
        $employee->update(['blocked' => $blocked]);
        return $employee->fresh();
    }
}
