<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnforceAuthorization
{
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();

        if (!$route) {
            return $next($request);
        }

        $controller = $route->getController();
        $action = $route->getActionMethod();

        if (!$controller || !method_exists($controller, $action)) {
            return $next($request);
        }

        $policy = $this->getPolicyForController($controller);

        if ($policy && class_exists($policy)) {
            $ability = $this->getAbilityForAction($action);
            $model = $this->getModelFromRoute($request);

            if (!Gate::allows($ability, $model)) {
                Log::warning('Authorization failed', [
                    'user_id' => $request->user()?->id,
                    'ability' => $ability,
                    'route' => $request->route()->getName(),
                ]);

                abort(403, 'This action is unauthorized.');
            }
        }

        return $next($request);
    }

    protected function getPolicyForController($controller): ?string
    {
        $controllerClass = get_class($controller);
        $modelName = str_replace('Controller', '', class_basename($controllerClass));
        $modelName = Str::singular($modelName);

        return "App\\Policies\\{$modelName}Policy";
    }

    protected function getAbilityForAction(string $action): string
    {
        return match ($action) {
            'index' => 'viewAny',
            'show' => 'view',
            'create', 'store' => 'create',
            'edit', 'update' => 'update',
            'destroy' => 'delete',
            default => 'view',
        };
    }

    protected function getModelFromRoute(Request $request)
    {
        $route = $request->route();
        $parameters = $route->parameters();

        foreach ($parameters as $parameter) {
            if (is_object($parameter) && method_exists($parameter, 'getUserIdColumn')) {
                return $parameter;
            }
        }

        return null;
    }
}
