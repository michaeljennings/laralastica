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
     * @inheritDoc
     */
    public function driver($driver = null)
    {
        $driver = $driver ?: $this->getDefaultDriver();

        // By default illuminate manager's will create one version of the driver
        // and share that in the application. We want to create a new driver
        // instance each time it is called so here we override that functionality
        if (isset($this->drivers[$driver])) {
            return $this->createDriver($driver);
        }

        return parent::driver($driver);
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

        return new ElasticaDriver($client, new IndexPrefixer(), $config);
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
