<?php

use Oussema\HideByCountries\Middleware\GeoLocationMiddleware;

return [
    'frontend' => [
        'middleware-geolocation' => [
            'target' => GeoLocationMiddleware::class,
            'before' => ['typo3/cms-frontend/content-length-headers'],
        ],
    ],
];
