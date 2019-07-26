<?php

return [
    'development_mode' => env('APP_DEBUG', true),

    // database
    'database_connection_name' => 'mysql',
    'database_name' => env('DB_DATABASE'),
    'database_user' => env('DB_USERNAME'),
    'database_password' => env('DB_PASSWORD'),
    'database_host' => env('DB_HOST'),
    'database_driver' => 'pdo_mysql',
    'database_in_memory' => false,
    'enable_query_log' => false,

    // host does the db migrations, clients do not
    'data_mode' => 'host', // 'host' or 'client'

    // cache
    'redis_host' => 'redis',
    'redis_port' => 6379,

    // entities
    'entities' => [
        [
            'path' => __DIR__ . '/../src/Entities',
            'namespace' => 'Railroad\ActionLog\Entities',
        ],
    ],
];
