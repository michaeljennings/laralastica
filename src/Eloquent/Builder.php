<?php

namespace Michaeljennings\Laralastica\Eloquent;

use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Michaeljennings\Laralastica\LengthAwarePaginator;
use Michaeljennings\Laralastica\ResultCollection;

class Builder
{
    /**
     * The base eloquent query instance.
     *
     * @var BaseBuilder
     */
    protected $builder;

    /**
     * The total amount of hits matched.
     *
     * @var int
     */
    protected $totalHits;

    /**
     * The max score from the results.
     *
     * @var float
     */
    protected $maxScore;

    /**
     * The total time the elasticsearch query took.
     *
     * @var float
     */
    protected $totalTime;

    public function __construct(BaseBuilder $builder, int $totalHits = 0, float $maxScore = 0, float $totalTime = 0)
    {
        $this->builder = $builder;
        $this->totalHits = $totalHits;
        $this->maxScore = $maxScore;
        $this->totalTime = $totalTime;
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param array $columns
     * @return ResultCollection
     */
    public function get($columns = ['*'])
    {
        $collection = $this->builder->get($columns);

        $resultCollection = new ResultCollection($collection->all());

        $resultCollection->setQueryStats($this->totalHits, $this->maxScore, $this->totalTime);

        return $resultCollection;
    }

    /**
     * Paginate the given query.
     *
     * @param  int  $perPage
     * @param  array  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     * @throws \InvalidArgumentException
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $paginator = $this->builder->paginate($perPage, $columns, $pageName, $page);

        $resultsPaginator = new LengthAwarePaginator(
            $paginator->items(),
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage()
        );

        $resultsPaginator->setQueryStats($this->totalHits, $this->maxScore, $this->totalTime);

        return $resultsPaginator;
    }

    /**
     * Get the total amount of hits matched.
     *
     * @return int
     */
    public function totalHits()
    {
        return $this->totalHits;
    }

    /**
     * Get the max score matched.
     *
     * @return float
     */
    public function maxScore()
    {
        return $this->maxScore;
    }

    /**
     * Get the total time the elasticsearch query took.
     *
     * @return int
     */
    public function totalTime()
    {
        return $this->totalTime;
    }

    /**
     * Catch any uncaught methods and run them on the underlying builder
     * instance.
     *
     * @param string $method
     * @param array  $args
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        return $this->builder->$method(...$args);
    }
}