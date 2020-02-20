<?php

namespace Michaeljennings\Laralastica\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Michaeljennings\Laralastica\ResultCollection;

interface Builder
{
    /**
     * Execute the query.
     *
     * @param string|array $indices
     * @return ResultCollection
     */
    public function get($indices);

    /**
     * Execute the query and then paginate the results.
     *
     * @param string|array $indices
     * @param int          $page
     * @param int          $perPage
     * @param int          $offset
     * @return LengthAwarePaginator
     */
    public function paginate($indices, $page, $perPage, $offset);

    /**
     * Add a new query.
     *
     * @param mixed $query
     * @return Query
     */
    public function query($query);

    /**
     * Add a bool query.
     *
     * @param callable $callback
     * @return Query
     */
    public function bool(callable $callback);

    /**
     * Add a new filter.
     *
     * @param mixed $filter
     * @return \Michaeljennings\Laralastica\Contracts\Filter
     */
    public function filter($filter);

    /**
     * Add a new document to the provided type.
     *
     * @param string     $type
     * @param string|int $id
     * @param array      $data
     * @return \Michaeljennings\Laralastica\Builder
     */
    public function add($type, $id, array $data);

    /**
     * Add multiple documents to the elasticsearch type. The data array must be a
     * multidimensional array with the key as the desired id and the value as
     * the data to be added to the document.
     *
     * @param string $type
     * @param array  $data
     * @return \Michaeljennings\Laralastica\Builder
     */
    public function addMultiple($type, array $data);

    /**
     * Delete a document from the provided type.
     *
     * @param string     $type
     * @param string|int $id
     * @return \Michaeljennings\Laralastica\Builder
     */
    public function delete($type, $id);

    /**
     * Get all of the queries registered in the builder.
     *
     * @return array
     */
    public function getQueries();

    /**
     * Get the current driver.
     *
     * @return Driver
     */
    public function getDriver();
}
