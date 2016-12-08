<?php

namespace Michaeljennings\Laralastica;

use Elastica\Client;
use Illuminate\Support\Manager;
use Michaeljennings\Laralastica\Drivers\ArrayDriver;
use Michaeljennings\Laralastica\Drivers\ElasticaDriver;
use Michaeljennings\Laralastica\Exceptions\DriverNotSetException;

class ClientManager extends Manager
{
    /**
     * The laralastica config.
     *
     * @var array
     */
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Create the elastica driver.
     *
     * @return Client
     */
    protected function createElasticaDriver()
    {
        $config = isset($this->config['drivers']['elastica']) ? $this->config['drivers']['elastica'] : [];
        $client = new Client($config);

        return new ElasticaDriver($client);
    }

    /**
     * Create the array driver.
     *
     * @return ArrayDriver
     */
    protected function createArrayDriver()
    {
        return new ArrayDriver();
    }

    /**
     * Get the default driver name.
     *
     * @return string
     * @throws DriverNotSetException
     */
    public function getDefaultDriver()
    {
        if ( ! isset($this->config['driver'])) {
            throw new DriverNotSetException("You must set the default driver to connect to in the laralastica config.");
        }

        return $this->config['driver'];
    }
}