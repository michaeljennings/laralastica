<?php

namespace Michaeljennings\Laralastica;

use Michaeljennings\Laralastica\Contracts\Driver;

class Builder
{
    /**
     * The driver manager.
     *
     * @var ClientManager
     */
    protected $manager;

    /**
     * The currently selected driver.
     *
     * @var Driver
     */
    protected $driver;

    /**
     * @var array
     */
    protected $queries = [];

    public function __construct(ClientManager $manager)
    {
        $this->manager = $manager;
        $this->driver = $manager->driver();
    }

    /**
     * Get all of the queries to be run.
     *
     * @return array
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * Change the driver.
     *
     * @param string $driver
     * @return $this
     */
    public function driver($driver)
    {
        $this->driver = $this->manager->driver($driver);

        return $this;
    }

    /**
     * Create a new query object.
     *
     * @param mixed $query
     * @return Query
     */
    protected function newQuery($query)
    {
        return new Query($query);
    }

    /**
     * Catch any unspecified methods and run them on the selected
     * driver.
     *
     * @param string $method
     * @param array  $args
     * @return $this
     */
    public function __call($method, array $args)
    {
        call_user_func_array([$this->driver, $method], $args);

        return $this;
    }
}