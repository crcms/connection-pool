<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Connection Pool Connection Name
    |--------------------------------------------------------------------------
    |
    */

    'default' => 'client',

    /*
    |--------------------------------------------------------------------------
    | Connection Pool Connections
    |--------------------------------------------------------------------------
    |
    | Choose a different connection pool configuration
    | max_idle_number: maximum number of idles
    | min_idle_number: minimum number of idles
    | max_connection_number: maximum number of connections
    | max_connection_time: maximum connection time (seconds)
    |
    */

    'connections' => [
        'client' => [
            'pool' => [
                'max_idle_number' => 1000,
                'min_idle_number' => 100,
                'max_connection_number' => 800,
                'max_connection_time' => 1,
            ],
            'factory' => 'factory',
            'connection' => 'http',
        ],
    ],
];