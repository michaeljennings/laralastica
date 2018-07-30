<?php

namespace Michaeljennings\Laralastica;

use Elastica\Client;
use Illuminate\Support\Manager;
use Michaeljennings\Laralastica\Drivers\ArrayDriver;
use Michaeljennings\Laralastica\Drivers\ElasticaDriver;
use Michaeljennings\Laralastica\Drivers\NullDriver;
use Michaeljennings\Laralastica\Exceptions\DriverNotSetException;
use Michaeljennings\Laralastica\Exceptions\IndexNotSetException;

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
     * @throws IndexNotSetException
     */
    protected function createElasticaDriver()
    {
        $config = isset($this->config['drivers']['elastica']) ? $this->config['drivers']['elastica'] : [];
        $client = new Client($config);

        return new ElasticaDriver($client, $config);
    }

    /**
     * Create the null driver.
     *
     * @return ArrayDriver
     */
    protected function createNullDriver()
    {
        return new NullDriver();
    }

    /**
     * Get the default driver name.
     *
     * @return string
     * @throws DriverNotSetException
     */
    public function getDefaultDriver()
    {
        if ( ! array_key_exists('driver', $this->config)) {
            throw new DriverNotSetException("You must set the default driver to connect to in the laralastica config.");
        }

        if (is_null($this->config['driver'])) {
            $this->config['driver'] = 'null';
        }

        return $this->config['driver'];
    }
}