<?php

namespace Michaeljennings\Laralastica;

trait HasQueryStats
{
    /**
     * The total results found.
     *
     * @var int
     */
    protected $totalHits;

    /**
     * The maximum score of the results
     *
     * @var float
     */
    protected $maxScore;

    /**
     * The total time taken to execute the query.
     *
     * @var float
     */
    protected $totalTime;

    /**
     * Set the stats for the elasticsearch query.
     *
     * @param int   $totalHits
     * @param int   $maxScore
     * @param float $totalTime
     * @return $this
     */
    public function setQueryStats($totalHits, $maxScore, $totalTime)
    {
        $this->totalHits = $totalHits;
        $this->maxScore = $maxScore;
        $this->totalTime = $totalTime;

        return $this;
    }

    /**
     * Get the total hits.
     *
     * @return int
     */
    public function totalHits()
    {
        return $this->totalHits;
    }

    /**
     * Get the max score of the results.
     *
     * @return float
     */
    public function maxScore()
    {
        return $this->maxScore;
    }

    /**
     * Get the time taken to execute the query.
     *
     * @return float
     */
    public function totalTime()
    {
        return $this->totalTime;
    }
}