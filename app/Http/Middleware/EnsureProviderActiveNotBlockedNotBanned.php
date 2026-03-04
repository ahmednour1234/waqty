<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Helpers\ApiResponse;

class EnsureProviderActiveNotBlockedNotBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        $provider = auth('provider')->user();

        if (!$provider) {
            return ApiResponse::error('api.auth.unauthenticated', 401);
        }

        if (!$provider->active) {
            return ApiResponse::error('api.auth.account_inactive', 403);
        }

        if ($provider->blocked) {
            return ApiResponse::error('api.auth.account_blocked', 403);
        }

        if ($provider->banned) {
            return ApiResponse::error('api.auth.account_banned', 403);
        }

        return $next($request);
    }
}
