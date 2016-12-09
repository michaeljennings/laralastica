<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Driver
     |--------------------------------------------------------------------------
     |
     | The driver controls which search connection gets used. This connection
     | will be used for all model syncing and searching.
     |
     | Supported: "elastica", "null"
     |
    */

    'driver' => 'elastica',

    /*
     |--------------------------------------------------------------------------
     | Index
     |--------------------------------------------------------------------------
     |
     | Set the name of the elasticsearch index you want to connect to. This must
     | be lower-case, alphanumeric, and must not contain any spaces, hyphens
     | or underscores.
     |
    */

    'index' => 'yourindex',

    /*
     |--------------------------------------------------------------------------
     | Drivers
     |--------------------------------------------------------------------------
     |
     | Set any driver specific configuration here.
     |
    */

    'drivers' => [
        'elastica' => [
            'hosts' => [
                'connectionStrategy' => 'RoundRobin',
                "connections" => [
                    [
                        'host' => 'localhost',
                        'port' => 9200
                    ]
                ]
            ],
            'size'  => 10,
        ]
    ]
];