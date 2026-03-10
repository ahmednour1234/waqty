<?php

$base = require __DIR__.'/scribe.php';

$base['title'] = config('app.name').' — Public API';
$base['description'] = 'Public API: categories, subcategories, countries, cities, providers, provider branches. No authentication.';
$base['routes'] = [
    [
        'match' => ['prefixes' => ['api/public/*'], 'domains' => ['*']],
        'include' => [],
        'exclude' => [],
    ],
];
$base['laravel']['add_routes'] = false;
$base['laravel']['docs_url'] = '/docs/public';
$base['laravel']['assets_directory'] = 'vendor/scribe_public';
$base['groups']['order'] = [
    'Public' => [
        'Categories' => [],
        'Subcategories' => [],
        'Countries' => [],
        'Cities' => [],
        'Providers' => [],
        'Provider Branches' => [],
    ],
];

return $base;
