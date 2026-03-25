<?php

namespace App\Http\Middleware;

use App\Http\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchActiveNotBlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        $branch = auth('branch')->user();

        if (!$branch) {
            return ApiResponse::error('api.auth.unauthenticated', 401);
        }

        if (!$branch->active) {
            return ApiResponse::error('api.auth.account_inactive', 403);
        }

        if ($branch->blocked) {
            return ApiResponse::error('api.auth.account_blocked', 403);
        }

        if ($branch->banned) {
            return ApiResponse::error('api.auth.account_banned', 403);
        }

        return $next($request);
    }
}
