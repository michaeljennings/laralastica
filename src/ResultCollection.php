<?php

namespace Michaeljennings\Laralastica;

use Illuminate\Support\Collection;
use Michaeljennings\Laralastica\Contracts\Result;

class ResultCollection extends Collection
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
     * @param Result[] $results
     * @param int      $totalHits
     * @param float    $maxScore
     * @param float    $totalTime
     */
    public function __construct(array $results, $totalHits, $maxScore, $totalTime)
    {
        $this->items = $results;
        $this->totalHits = $totalHits;
        $this->maxScore = $maxScore;
        $this->totalTime = $totalTime;
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