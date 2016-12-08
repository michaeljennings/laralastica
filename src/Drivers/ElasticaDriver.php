<?php

namespace Michaeljennings\Laralastica\Drivers;

use Elastica\Client;
use Michaeljennings\Laralastica\Contracts\Driver;

class ElasticaDriver implements Driver
{
    /**
     * The elastica client.
     *
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
}