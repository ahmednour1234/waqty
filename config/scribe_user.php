<?php

$base = require __DIR__.'/scribe.php';

$base['title'] = config('app.name').' — User API';
$base['description'] = 'User API: register, verify, resend, login, forgot password, reset password, session.';
$base['routes'] = [
    [
        'match' => ['prefixes' => ['api/user/*'], 'domains' => ['*']],
        'include' => [],
        'exclude' => [],
    ],
];
$base['laravel']['add_routes'] = false;
$base['laravel']['docs_url'] = '/docs/user';
$base['laravel']['assets_directory'] = 'vendor/scribe_user';
$base['groups']['order'] = [
    'User' => [
        'Auth - Register' => ['POST /user/auth/register'],
        'Auth - Verify / Resend' => ['POST /user/auth/verify-email', 'POST /user/auth/resend-verification-otp'],
        'Auth - Login' => ['POST /user/auth/login'],
        'Auth - Password' => ['POST /user/auth/forgot-password', 'POST /user/auth/verify-otp', 'POST /user/auth/reset-password'],
        'Auth - Session' => ['POST /user/auth/logout', 'GET /user/auth/me'],
    ],
];

return $base;
