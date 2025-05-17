<?php

declare(strict_types=1);

return [
    'config' => [
        'entity_paths' => [
            app_path('Entities'),
        ],
        'metadata' => [
            'cache' => true,
            'driver' => Laradom\ORM\Mapping\Driver\AttributeDriver::class,
        ],
        'naming_strategy' => Laradom\ORM\Mapping\Naming\DefaultNamingStrategy::class,
    ],
];
