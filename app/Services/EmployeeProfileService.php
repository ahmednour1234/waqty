<?php

namespace App\Services;

use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeProfileService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private ImageUploadService $imageUploadService
    ) {
    }

    public function updateProfile(array $data, ?UploadedFile $logo = null)
    {
        $employee = Auth::guard('employee')->user();

        return DB::transaction(function () use ($data, $logo, $employee) {
            $oldLogoPath = $employee->logo_path;

            if ($logo) {
                $provider = $employee->provider;
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

    public function changePassword(string $currentPassword, string $newPassword): void
    {
        $employee = Auth::guard('employee')->user();

        if (!Hash::check($currentPassword, $employee->password)) {
            throw new \Exception('api.auth.current_password_incorrect', 400);
        }

        $this->employeeRepository->update($employee, ['password' => $newPassword]);
    }
}
