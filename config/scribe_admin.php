<?php

$base = require __DIR__.'/scribe.php';

$base['title'] = config('app.name').' — Admin API';
$base['description'] = 'Admin API: authentication (send OTP, verify, resend, login), admins, categories, countries, cities, providers, employees.';
$base['routes'] = [
    [
        'match' => ['prefixes' => ['api/admin/*'], 'domains' => ['*']],
        'include' => [],
        'exclude' => [],
    ],
];
$base['laravel']['add_routes'] = false;
$base['laravel']['docs_url'] = '/docs/admin';
$base['laravel']['assets_directory'] = 'vendor/scribe_admin';
$base['groups']['order'] = [
    'Admin' => [
        'Auth - Verification' => ['POST /admin/auth/send-verification-otp', 'POST /admin/auth/verify-email', 'POST /admin/auth/resend-verification-otp'],
        'Auth - Login' => ['POST /admin/auth/login'],
        'Auth - Session' => ['POST /admin/auth/logout', 'GET /admin/auth/me'],
        'Admins' => [],
        'Categories' => [],
        'Subcategories' => [],
        'Countries' => [],
        'Cities' => [],
        'Providers' => [],
        'Provider Branches' => [],
        'Employees' => [],
        'Services' => [],
        'Shifts' => [],
    ],
];

return $base;
