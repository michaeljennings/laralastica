<?php

namespace Michaeljennings\Laralastica\Contracts;

interface Filter
{
    /**
     * Get the filter(s).
     *
     * @return Builder|mixed
     */
    public function getFilter();
}