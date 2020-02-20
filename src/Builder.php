<?php

namespace Michaeljennings\Laralastica;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Michaeljennings\Laralastica\Contracts\Builder as BuilderContract;
use Michaeljennings\Laralastica\Contracts\Driver;
use Michaeljennings\Laralastica\Exceptions\DriverMethodDoesNotExistException;

class Builder implements BuilderContract
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
     * @param string|array $indices
     * @return ResultCollection
     */
    public function get($indices)
    {
        return $this->driver->get($indices, $this->queries);
    }

    /**
     * Execute the query and then paginate the results.
     *
     * @param string|array $indices
     * @param int          $page
     * @param int          $perPage
     * @param int          $offset
     * @return LengthAwarePaginator
     */
    public function paginate($indices, $page, $perPage, $offset)
    {
        return $this->driver->paginate($indices, $this->queries, $page, $perPage, $offset);
    }

    /**
     * Add a new query.
     *
     * @param mixed $query
     * @return \Michaeljennings\Laralastica\Contracts\Query
     */
    public function query($query)
    {
        return $this->queries[] = $this->newQuery($query);
    }

    /**
     * Add a new filter.
     *
     * @param mixed $filter
     * @return \Michaeljennings\Laralastica\Contracts\Filter
     */
    public function filter($filter)
    {
        if (is_callable($filter)) {
            $builder = new static($this->manager);

            $filter($builder);

            return $this->queries[] = $this->newFilter($builder);
        }

        return $this->queries[] = $this->newFilter($filter);
    }

    /**
     * Add a new document to the provided index.
     *
     * @param string     $index
     * @param string|int $id
     * @param array      $data
     * @return $this
     */
    public function add($index, $id, array $data)
    {
        $this->driver->add($index, $id, $data);

        return $this;
    }

    /**
     * Add multiple documents to the elasticsearch index. The data must be an
     * associative array with the key as the desired id and the value as the
     * data to be added to the document.
     *
     * @param string $index
     * @param array  $data
     * @return $this
     */
    public function addMultiple($index, array $data)
    {
        $this->driver->addMultiple($index, $data);

        return $this;
    }

    /**
     * Delete a document from the provided index.
     *
     * @param string     $index
     * @param string|int $id
     * @return $this
     */
    public function delete($index, $id)
    {
        $this->driver->delete($index, $id);

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
     * Create a new filter.
     *
     * @param mixed $filter
     * @return Filter
     */
    protected function newFilter($filter)
    {
        return new Filter($filter);
    }

    /**
     * Get all of the queries registered in the builder.
     *
     * @return array
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * Catch any unspecified methods and run them on the selected
     * driver.
     *
     * @param string $method
     * @param array  $args
     * @return $this|Driver
     * @throws DriverMethodDoesNotExistException
     */
    public function __call($method, array $args)
    {
        if (! method_exists($this->driver, $method)) {
            throw new DriverMethodDoesNotExistException("The {$method} method does not exist on the current driver");
        }

        $query = call_user_func_array([$this->driver, $method], $args);

        if (! $query || $query instanceof Driver) {
            return $query;
        }

        return $this->query($query);
    }
}
