<?php

namespace Michaeljennings\Laralastica;

use Elastica\ResultSet;
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
     * @return ResultContainer
     */
    public function get($types)
    {
        $query = $this->driver->get($types, $this->queries);

        return $this->newResultContainer($query);
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
     * Create a new result container.
     *
     * @param ResultSet $results
     * @return ResultContainer
     */
    protected function newResultContainer(ResultSet $results)
    {
        return new ResultContainer($results);
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