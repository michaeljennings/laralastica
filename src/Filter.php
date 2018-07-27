<?php

namespace Michaeljennings\Laralastica;

use Michaeljennings\Laralastica\Contracts\Builder;
use Michaeljennings\Laralastica\Contracts\Filter as FilterContract;

class Filter implements FilterContract
{
    /**
     * The filter(s) to be applied.
     *
     * @var Builder|mixed
     */
    protected $filter;

    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    /**
     * Get the filter(s).
     *
     * @return Builder|mixed
     */
    public function getFilter()
    {
        return $this->filter;
    }
}