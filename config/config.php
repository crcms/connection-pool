<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Connection Pool Connection Name
    |--------------------------------------------------------------------------
    |
    */

    'default' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Connection Pool Connections
    |--------------------------------------------------------------------------
    |
    | Choose a different connection pool configuration
    | max_idle_number: maximum number of idles
    | min_idle_number: minimum number of idles
    | max_connection_number: maximum number of connections
    |
    */

    'connections' => [
        'default' => [
            'max_idle_number' => 50,
            'min_idle_number' => 3,
            'max_connection_number' => 50,
        ],
    ],
];