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
}