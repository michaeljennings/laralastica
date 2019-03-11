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

    'driver' => env('ELASTICSEARCH_DRIVER', 'elastica'),

    /*
     |---------------------------------------------------------------------
     | Index Prefix
     |---------------------------------------------------------------------
     |
     | As of elasticsearch 6 you can no longer have multiple types within
     | a singular index so we now create an index per model. However this
     | may cause issues if you have multiple environments.
     |
     | Here you can set a prefix that will be added to every index we
     | create, this allows you to have a dev index, testing index etc.
     |
    */

    'index_prefix' => env('ELASTICSEARCH_INDEX_PREFIX'),

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
            'servers' => [
                [
                    'host' => env('ELASTICSEARCH_HOST', 'localhost'),
                    'port' => env('ELASTICSEARCH_PORT', 9200)
                ]
            ],
            'size'  => 10,
        ]
    ],

    /*
     |--------------------------------------------------------------------------
     | Indexable Models
     |--------------------------------------------------------------------------
     |
     | Here you can set any models you want to be re-indexed when the
     | laralastica:index command is run.
     |
     | The models are added as an associative array, with the key being a custom
     | string to reference the model by, and the value being the full namespace
     | of the model.
     |
     | If you want to re-index just one of the models, then pass the key through
     | to the command like so - laralastica:index {key}
     |
    */

    'indexable' => [
        'users' => \App\User::class,
    ]

];
