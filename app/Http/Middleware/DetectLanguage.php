<?php

namespace App\Http\Middleware;

use App\Http\Helpers\LocalizationHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectLanguage
{
    public function handle(Request $request, Closure $next): Response
    {
        $language = LocalizationHelper::getCurrentLanguage($request);
        app()->setLocale($language);
        $request->merge(['_detected_language' => $language]);

        return $next($request);
    }
}
