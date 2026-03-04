<?php

return [
    'max_file_size' => env('UPLOAD_MAX_SIZE', 2048), // in KB (2MB default)

    'allowed_mime_types' => [
        'image/jpeg',
        'image/png',
        'image/webp',
    ],

    'allowed_extensions' => [
        'jpg',
        'jpeg',
        'png',
        'webp',
    ],

    'storage_path' => [
        'categories' => 'categories',
        'subcategories' => 'subcategories',
        'providers' => 'providers',
        'employees' => 'providers',
    ],

    'output_format' => 'webp',
];
