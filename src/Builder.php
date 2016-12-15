<?php

namespace Michaeljennings\Laralastica;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Michaeljennings\Laralastica\Contracts\Builder as BuilderContract;
use Michaeljennings\Laralastica\Contracts\Driver;

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
     * @param string|array $types
     * @return ResultCollection
     */
    public function get($types)
    {
        return $this->driver->get($types, $this->queries);
    }

    /**
     * Execute the query and then paginate the results.
     *
     * @param string|array $types
     * @param int          $page
     * @param int          $perPage
     * @param int          $offset
     * @return LengthAwarePaginator
     */
    public function paginate($types, $page, $perPage, $offset)
    {
        return $this->driver->paginate($types, $this->queries, $page, $perPage, $offset);
    }

    /**
     * Add a new query.
     *
     * @param mixed $query
     * @return $this
     */
    public function query($query)
    {
        return $this->queries[] = $this->newQuery($query);
    }

    /**
     * Add a new document to the provided type.
     *
     * @param string     $type
     * @param string|int $id
     * @param array      $data
     * @return $this
     */
    public function add($type, $id, array $data)
    {
        $this->driver->add($type, $id, $data);

        return $this;
    }

    /**
     * Add multiple documents to the elasticsearch type. The data array must be a
     * multidimensional array with the key as the desired id and the value as
     * the data to be added to the document.
     *
     * @param string $type
     * @param array  $data
     * @return $this
     */
    public function addMultiple($type, array $data)
    {
        $this->driver->addMultiple($type, $data);

        return $this;
    }

    /**
     * Delete a document from the provided type.
     *
     * @param string     $type
     * @param string|int $id
     * @return $this
     */
    public function delete($type, $id)
    {
        $this->driver->delete($type, $id);

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

        return $this->query($query);
    }
}