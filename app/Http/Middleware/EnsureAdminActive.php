<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Helpers\ApiResponse;

class EnsureAdminActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = auth('admin')->user();

        if (!$admin) {
            return ApiResponse::error('api.auth.unauthenticated', 401);
        }

        if (!$admin->active) {
            return ApiResponse::error('api.auth.account_inactive', 403);
        }

        return $next($request);
    }
}
