<?php

namespace Michaeljennings\Laralastica;

use Michaeljennings\Laralastica\Contracts\Filter as FilterContract;

class Filter implements FilterContract
{
    /**
     * The filter(s) to be applied.
     *
     * @var mixed
     */
    protected $filter;

    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    /**
     * Get the filter(s).
     *
     * @return mixed
     */
    public function getFilter()
    {
        return $this->filter;
    }
}