<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserActiveNotBlockedNotBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('user');

        if (! $user || $user->trashed()) {
            return $this->forbiddenResponse(__('api.general.forbidden'));
        }

        if (! $user->active) {
            return $this->forbiddenResponse(__('api.auth.account_inactive'));
        }

        if ($user->blocked) {
            return $this->forbiddenResponse(__('api.auth.account_blocked'));
        }

        if ($user->banned) {
            return $this->forbiddenResponse(__('api.auth.account_banned'));
        }

        return $next($request);
    }

    protected function forbiddenResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => null,
        ], Response::HTTP_FORBIDDEN);
    }
}
