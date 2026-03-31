<?php

namespace App\Services;

use App\Models\ProviderBranch;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProviderEmployeeService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private ImageUploadService $imageUploadService
    ) {
    }

    public function index(array $filters = [], int $perPage = 15)
    {
        $provider = Auth::guard('provider')->user();
        return $this->employeeRepository->paginateProvider($provider->id, $filters, $perPage);
    }

    public function store(array $data, ?UploadedFile $logo = null)
    {
        $provider = Auth::guard('provider')->user();

        return DB::transaction(function () use ($data, $logo, $provider) {
            if (isset($data['branch_uuid'])) {
                $branch = ProviderBranch::whereUuid($data['branch_uuid'])->first();
                if (!$branch || $branch->provider_id !== $provider->id) {
                    throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Branch not found');
                }
                $data['branch_id'] = $branch->id;
                unset($data['branch_uuid']);
            }

            $data['provider_id'] = $provider->id;
            $data['has_app_access'] = !empty($data['password']);

            $employee = $this->employeeRepository->create($data);

            if ($logo) {
                $directory = 'providers/' . $provider->uuid . '/employees/' . $employee->uuid;
                $imagePath = $this->imageUploadService->processImage($logo, $directory);
                $employee = $this->employeeRepository->update($employee, ['logo_path' => $imagePath]);
            }

            return $employee;
        });
    }

    public function show(string $uuid)
    {
        $provider = Auth::guard('provider')->user();
        $employee = $this->employeeRepository->findByUuid($uuid);

        if (!$employee || $employee->provider_id !== $provider->id) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Employee not found');
        }

        return $employee;
    }

    public function update(string $uuid, array $data, ?UploadedFile $logo = null)
    {
        $provider = Auth::guard('provider')->user();

        return DB::transaction(function () use ($uuid, $data, $logo, $provider) {
            $employee = $this->employeeRepository->findByUuid($uuid);

            if (!$employee || $employee->provider_id !== $provider->id) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Employee not found');
            }

            if (isset($data['branch_uuid'])) {
                $branch = ProviderBranch::whereUuid($data['branch_uuid'])->first();
                if (!$branch || $branch->provider_id !== $provider->id) {
                    throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Branch not found');
                }
                $data['branch_id'] = $branch->id;
                unset($data['branch_uuid']);
            }

            if (array_key_exists('password', $data)) {
                $data['has_app_access'] = !empty($data['password']);
            }

            $oldLogoPath = $employee->logo_path;

            if ($logo) {
                $directory = 'providers/' . $provider->uuid . '/employees/' . $employee->uuid;
                $imagePath = $this->imageUploadService->processImage($logo, $directory);
                $data['logo_path'] = $imagePath;

                if ($oldLogoPath) {
                    $this->imageUploadService->deleteImage($oldLogoPath);
                }
            }

            return $this->employeeRepository->update($employee, $data);
        });
    }

    public function destroy(string $uuid): bool
    {
        $provider = Auth::guard('provider')->user();
        $employee = $this->employeeRepository->findByUuid($uuid);

        if (!$employee || $employee->provider_id !== $provider->id) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Employee not found');
        }

        return $this->employeeRepository->softDelete($employee);
    }

    public function toggleActive(string $uuid, bool $active)
    {
        $provider = Auth::guard('provider')->user();
        $employee = $this->employeeRepository->findByUuid($uuid);

        if (!$employee || $employee->provider_id !== $provider->id) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Employee not found');
        }

        return $this->employeeRepository->toggleActive($employee, $active);
    }

    public function block(string $uuid, bool $blocked)
    {
        $provider = Auth::guard('provider')->user();
        $employee = $this->employeeRepository->findByUuid($uuid);

        if (!$employee || $employee->provider_id !== $provider->id) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Employee not found');
        }

        return $this->employeeRepository->setBlocked($employee, $blocked);
    }
}
