<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\EmployeeChangePasswordRequest;
use App\Http\Requests\Employee\EmployeeUpdateProfileRequest;
use App\Http\Resources\Employee\EmployeeSelfResource;
use App\Http\Helpers\ApiResponse;
use App\Services\EmployeeProfileService;
use Illuminate\Http\JsonResponse;

class EmployeeProfileController extends Controller
{
    public function __construct(
        private EmployeeProfileService $profileService
    ) {
    }

    public function updateProfile(EmployeeUpdateProfileRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $logo = $request->file('logo');
            $employee = $this->profileService->updateProfile($data, $logo);

            return ApiResponse::success(
                new EmployeeSelfResource($employee->load('branch')),
                'api.employees.updated'
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function changePassword(EmployeeChangePasswordRequest $request): JsonResponse
    {
        try {
            $this->profileService->changePassword(
                $request->validated()['current_password'],
                $request->validated()['new_password']
            );

            return ApiResponse::success(null, 'api.auth.password_reset_success');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
