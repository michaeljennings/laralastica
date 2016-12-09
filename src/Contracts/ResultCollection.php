<?php

namespace Michaeljennings\Laralastica\Contracts;

interface ResultCollection
{
    /**
     * Set the stats for the elasticsearch query.
     *
     * @param int   $totalHits
     * @param int   $maxScore
     * @param float $totalTime
     * @return \Michaeljennings\Laralastica\ResultCollection
     */
    public function setQueryStats($totalHits, $maxScore, $totalTime);

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