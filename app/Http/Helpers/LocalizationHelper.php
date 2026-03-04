<?php

namespace App\Http\Helpers;

use Illuminate\Http\Request;

class LocalizationHelper
{
    public static function getCurrentLanguage(Request $request): string
    {
        $acceptLanguage = $request->header('Accept-Language', 'ar');
        $languages = ['ar', 'en'];
        
        $preferred = strtolower(substr($acceptLanguage, 0, 2));
        
        return in_array($preferred, $languages) ? $preferred : 'ar';
    }

    public static function getLocalizedName(array $nameJson, ?string $lang = null): ?string
    {
        if ($lang === null) {
            $lang = app()->getLocale();
        }

        return $nameJson[$lang] ?? $nameJson['ar'] ?? $nameJson['en'] ?? null;
    }

    public static function validateMultilingualName(array $name): bool
    {
        return isset($name['ar']) && isset($name['en']) &&
               is_string($name['ar']) && is_string($name['en']);
    }
}
