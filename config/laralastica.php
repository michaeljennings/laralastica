<?php

return [

    /**
     * The name of the elasticsearch index, must be lower case, alphanumeric, and
     * must not contain any spaces, hyphens or underscores.
     */
    'index' => 'yourindex',

    /**
     * The host of your elasticsearch cluster.
     */
    'host' => 'localhost',

    /**
     * The port your elasticsearch cluster is running on.
     */
    'port' => 9200,

    /**
     * Alternatively use a url to connect, must contain a trailing slash
     */
    //'url' => 'https://user:pass@your-search.com/',

    /**
     * The maximum amount of results to return for a query.
     */
    'size' => 10,

    /**
     * Register which models correspond to which elasticsearch types. The key
     * should be the elasticsearch type and the value should be the model.
     *
     * i.e. 'testType' => 'App\TestType'
     */
    'types' => [
        //
    ],

];