<?php

declare(strict_types=1);

return [
    'resource_paths' => [
        'app/Resources',
        'Modules/*/Resources',
    ],

    // Cache TTL for registry scan results in seconds.
    // 0 = disabled (scan every request). Recommended: 3600 in production.
    'cache_ttl' => (int) env('DDT_REGISTRY_CACHE_TTL', 0),
];
