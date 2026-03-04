<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Helpers\ApiResponse;

class EnsureEmployeeActiveNotBlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        $employee = auth('employee')->user();

        if (!$employee) {
            return ApiResponse::error('api.auth.unauthenticated', 401);
        }

        if (!$employee->active) {
            return ApiResponse::error('api.auth.account_inactive', 403);
        }

        if ($employee->blocked) {
            return ApiResponse::error('api.auth.account_blocked', 403);
        }

        return $next($request);
    }
}
