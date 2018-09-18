<?php

namespace Michaeljennings\Laralastica;

trait SearchSoftDeletes
{
    use Searchable;

    /**
     * Run the elasticsearch query and ignore soft deleted results.
     *
     * @param callable $query
     * @param callable $searchQuery
     * @param string|null $key
     * @param bool $sortByResults
     * @return mixed
     */
    public function scopeSearch($query, callable $searchQuery, $key = null, $sortByResults = true)
    {
        $searchQuery = $this->addSoftDeleteQuery($searchQuery, 'mustNot');

        return $this->runSearch($query, $searchQuery, $key, $sortByResults);
    }

    /**
     * Run the elasticsearch query and match both soft deleted and
     * non-soft deleted results.
     *
     * @param callable $query
     * @param callable $searchQuery
     * @param string|null $key
     * @param bool $sortByResults
     * @return mixed
     */
    public function scopeSearchWithTrashed($query, callable $searchQuery, $key = null, $sortByResults = true)
    {
        $query->withTrashed();

        return $this->runSearch($query, $searchQuery, $key, $sortByResults);
    }

    /**
     * Run the elasticsearch query and only get soft deleted results.
     *
     * @param callable $query
     * @param callable $searchQuery
     * @param string|null $key
     * @param bool $sortByResults
     * @return mixed
     */
    public function scopeSearchOnlyTrashed($query, callable $searchQuery, $key = null, $sortByResults = true)
    {
        $searchQuery = $this->addSoftDeleteQuery($searchQuery, 'must');

        $query->onlyTrashed();

        return $this->runSearch($query, $searchQuery, $key, $sortByResults);
    }

    /**
     * Add the soft delete query to the builder query.
     *
     * @param callable $searchQuery
     * @param string $level
     * @return callable
     */
    protected function addSoftDeleteQuery(callable $searchQuery, $level)
    {
        return function ($builder) use ($searchQuery, $level) {
            $searchQuery($builder);

            $builder->filter(function($builder) use ($level) {
                $builder->exists($this->getDeletedAtColumn())->$level();
            });
        };
    }
}