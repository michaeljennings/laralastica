<?php

namespace Michaeljennings\Laralastica;

use Elastica\Query\AbstractQuery;
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
     * Execute the query.
     *
     * @param string|array $types
     * @return ResultCollection
     */
    public function get($types)
    {
        return $this->driver->get($types, $this->queries);
    }

    /**
     * Add a new query.
     *
     * @param mixed $query
     * @return $this
     */
    public function query($query)
    {
        $this->queries[] = $this->newQuery($query);

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
        $query = call_user_func_array([$this->driver, $method], $args);

        $this->query($query);

        return $this;
    }
}