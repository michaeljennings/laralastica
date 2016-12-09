<?php

namespace Michaeljennings\Laralastica\Contracts;

interface ResultCollection
{
    /**
     * Get the total hits.
     *
     * @return int
     */
    public function totalHits();

    /**
     * Get the max score of the results.
     *
     * @return float
     */
    public function maxScore();

    /**
     * Get the time taken to execute the query.
     *
     * @return float
     */
    public function totalTime();
}