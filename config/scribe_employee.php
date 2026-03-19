<?php

$base = require __DIR__.'/scribe.php';

$base['title'] = config('app.name').' — Employee API';
$base['description'] = 'Employee API: login, verification OTP, password reset, profile.';
$base['routes'] = [
    [
        'match' => ['prefixes' => ['api/employee/*'], 'domains' => ['*']],
        'include' => [],
        'exclude' => [],
    ],
];
$base['laravel']['add_routes'] = false;
$base['laravel']['docs_url'] = '/docs/employee';
$base['laravel']['assets_directory'] = 'vendor/scribe_employee';
$base['groups']['order'] = [
    'Employee' => [
        'Auth - Login' => ['POST /employee/auth/login'],
        'Auth - Verification' => ['POST /employee/auth/send-verification-otp', 'POST /employee/auth/verify-email', 'POST /employee/auth/resend-verification-otp'],
        'Auth - Password' => ['POST /employee/auth/send-otp', 'POST /employee/auth/verify-otp', 'POST /employee/auth/forgot-password', 'POST /employee/auth/reset-password'],
        'Auth - Session' => ['POST /employee/auth/logout', 'GET /employee/auth/me'],
        'Profile' => [],
        'Shifts' => [],
    ],
];

return $base;
