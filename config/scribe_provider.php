<?php

$base = require __DIR__.'/scribe.php';

$base['title'] = config('app.name').' — Provider API';
$base['description'] = 'Provider API: register, verify, resend, login, password reset, profile, branches, employees.';
$base['routes'] = [
    [
        'match' => ['prefixes' => ['api/provider/*'], 'domains' => ['*']],
        'include' => [],
        'exclude' => [],
    ],
];
$base['laravel']['add_routes'] = false;
$base['laravel']['docs_url'] = '/docs/provider';
$base['laravel']['assets_directory'] = 'vendor/scribe_provider';
$base['groups']['order'] = [
    'Provider' => [
        'Auth - Register' => ['POST /provider/auth/register'],
        'Auth - Verify / Resend' => ['POST /provider/auth/verify-email', 'POST /provider/auth/resend-verification-otp'],
        'Auth - Login' => ['POST /provider/auth/login'],
        'Auth - Password' => ['POST /provider/auth/send-otp', 'POST /provider/auth/verify-otp', 'POST /provider/auth/reset-password'],
        'Auth - Session' => ['POST /provider/auth/logout', 'GET /provider/auth/me'],
        'Profile' => [],
        'Branches' => [],
        'Employees' => [],
        'Services' => [],
        'Shift Templates' => [],
        'Shifts' => [],
    ],
];

return $base;
