<?php

namespace Michaeljennings\Laralastica\Contracts;

use Michaeljennings\Laralastica\ResultCollection;

interface Laralastica
{
    /**
     * Search for the results using the provided callback.
     *
     * @param string|array $types
     * @param callable     $query
     * @return ResultCollection
     */
    public function search($types, callable $query);

    /**
     * Search and paginate the results.
     *
     * @param string|array $types
     * @param callable     $query
     * @param int          $perPage
     * @return ResultCollection
     */
    public function paginate($types, callable $query, $perPage);

    /**
     * Add a new document to the provided type.
     *
     * @param string     $type
     * @param string|int $id
     * @param array      $data
     * @return \Michaeljennings\Laralastica\Contracts\Laralastica
     */
    public function add($type, $id, array $data);

    /**
     * Add multiple documents to the elasticsearch type. The data array must be a
     * multidimensional array with the key as the desired id and the value as
     * the data to be added to the document.
     *
     * @param string $type
     * @param array  $data
     * @return \Michaeljennings\Laralastica\Contracts\Laralastica
     */
    public function addMultiple($type, array $data);

    /**
     * Delete a document from the provided type.
     *
     * @param string     $type
     * @param string|int $id
     * @return \Michaeljennings\Laralastica\Contracts\Laralastica
     */
    public function delete($type, $id);
}