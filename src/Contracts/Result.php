<?php

namespace Michaeljennings\Laralastica\Contracts;

interface Result
{
    /**
     * Execute the query.
     *
     * @param string|array $types
     * @return ResultCollection
     */
    public function get($types);
}