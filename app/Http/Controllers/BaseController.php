<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

abstract class BaseController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->validateUserContext($request);
            return $next($request);
        });
    }

    protected function validateUserContext(Request $request): void
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }
    }

    protected function authorizeAction(string $ability, $arguments = []): void
    {
        if (!Gate::allows($ability, $arguments)) {
            abort(403, 'This action is unauthorized.');
        }
    }
}
