<?php namespace Michaeljennings\Laralastica\Contracts;

use Closure;

interface Wrapper {

    /**
     * Add a new document to the provided type.
     *
     * @param string $type
     * @param string|int $id
     * @param array $data
     * @return $this
     */
    public function add($type, $id, array $data);

    /**
     * Add multiple documents to the elasticsearch type. The data array must be a
     * multidimensional array with the key as the desired id and the value as
     * the data to be added to the document.
     *
     * @param string $type
     * @param array $data
     * @return $this
     */
    public function addMultiple($type, array $data);

    /**
     * Run the provided queries on the type and then return the results.
     *
     * @param string $type
     * @param callable $query
     * @return mixed
     */
    public function search($type, Closure $query);

}