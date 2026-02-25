<?php

declare(strict_types=1);

return [
    'api_prefix' => 'api',

    'pagination' => [
        'per_page'     => 25,
        'max_per_page' => 200,
    ],

    'resource_paths' => [
        'app/Resources',
        'Modules/*/Resources',
    ],
];
