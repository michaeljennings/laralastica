<?php namespace Michaeljennings\Laralastica\Contracts;

use Closure;
use Michaeljennings\Laralastica\Laralastica;

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
     * Run the provided queries on the types and then return the results.
     *
     * @param string|array $types
     * @param callable $query
     * @param null|int $limit
     * @param null|int $offset
     * @return mixed
     */
    public function search($types, Closure $query, $limit = null, $offset = null);

    /**
     * Delete a document from the provided type.
     *
     * @param string $type
     * @param string|int $id
     * @return $this
     */
    public function delete($type, $id);

}