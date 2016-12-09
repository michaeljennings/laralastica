<?php

namespace Michaeljennings\Laralastica;

use Illuminate\Pagination\LengthAwarePaginator as BaseLengthAwarePaginator;
use Michaeljennings\Laralastica\Contracts\ResultCollection as ResultCollectionContract;

class LengthAwarePaginator extends BaseLengthAwarePaginator implements ResultCollectionContract
{
    /**
     * The total hits matched by the elasticsearch query.
     *
     * @var int|null
     */
    protected $totalHits = null;

    /**
     * The maximum score matched by the query.
     *
     * @var int|null
     */
    protected $maxScore = null;

    /**
     * The time taken to execute the elasticsearch query.
     *
     * @var float|null
     */
    protected $totalTime = null;

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