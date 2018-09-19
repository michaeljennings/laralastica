<?php

namespace Michaeljennings\Laralastica\Drivers;

use Michaeljennings\Laralastica\Contracts\AbstractQuery;
use Michaeljennings\Laralastica\Contracts\Driver;
use Michaeljennings\Laralastica\Contracts\ElasticaDriver;
use Michaeljennings\Laralastica\LengthAwarePaginator;
use Michaeljennings\Laralastica\ResultCollection;

class NullDriver implements Driver
{
    /**
     * Execute the provided queries.
     *
     * @param string|array $indices
     * @param array        $queries
     * @return ResultCollection
     */
    public function get($indices, array $queries)
    {
        $collection = new ResultCollection([]);

        return $collection->setQueryStats(0, 0, 0);
    }

    /**
     * Execute the query and return a paginated list of results.
     *
     * @param string|array $indices
     * @param array        $queries
     * @param int          $page
     * @param int          $perPage
     * @param int          $offset
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($indices, array $queries, $page, $perPage, $offset)
    {
        $paginator = new LengthAwarePaginator([], 0, 1, 1);

        return $paginator->setQueryStats(0, 0, 0);
    }

    /**
     * Create a common query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-common-terms-query.html
     *
     * @param string        $field
     * @param string        $query
     * @param float         $cutoffFrequency
     * @param callable|null $callback
     * @return mixed
     */
    public function common(string $field, string $query, float $cutoffFrequency, callable $callback = null)
    {
        //
    }

    /**
     * Create a new exists query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-exists-query.html
     *
     * @param string        $key
     * @param callable|null $callback
     * @return AbstractQuery
     */
    public function exists(string $key, callable $callback = null)
    {
        //
    }

    /**
     * Create a new fuzzy query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-fuzzy-query.html
     *
     * @param string        $field
     * @param string        $value
     * @param callable|null $callback
     * @return mixed
     */
    public function fuzzy(string $field, string $value, callable $callback = null)
    {
        //
    }

    /**
     * Create a new match query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query.html
     *
     * @param string|null   $field
     * @param string|null   $value
     * @param callable|null $callback
     * @return mixed
     */
    public function match(string $field = null, string $value = null, callable $callback = null)
    {
        //
    }


    /**
     * Create a new match phrase query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query-phrase.html
     *
     * @param string|null   $field
     * @param string|null   $value
     * @param callable|null $callback
     * @return null
     */
    public function matchPhrase(string $field = null, string $value = null, callable $callback = null)
    {
        //
    }

    /**
     * Create a new match phrase prefix query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query-phrase-prefix.html
     *
     * @param string|null   $field
     * @param string|null   $value
     * @param callable|null $callback
     * @return null
     */
    public function matchPhrasePrefix(string $field = null, string $value = null, callable $callback = null)
    {
        //
    }

    /**
     * Create a match all query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-all-query.html
     *
     * @return mixed
     */
    public function matchAll()
    {
        //
    }

    /**
     * Create a new multi match query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/6.2/query-dsl-multi-match-query.html
     *
     * @param array|null    $fields
     * @param string|null   $value
     * @param callable|null $callback
     * @return null
     */
    public function multiMatch(array $fields = null, string $value = null, callable $callback = null)
    {
        //
    }

    /**
     * Create a query string query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html
     *
     * @param string        $query
     * @param callable|null $callback
     * @return mixed
     */
    public function queryString(string $query = '', callable $callback = null)
    {
        //
    }

    /**
     * Create a range query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html
     *
     * @param null|string   $fieldName
     * @param array         $args
     * @param callable|null $callback
     * @return mixed
     */
    public function range(string $fieldName = null, array $args = [], callable $callback = null)
    {
        //
    }

    /**
     * Create a new regular expression query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-regexp-query.html
     *
     * @param string        $key
     * @param string|null   $value
     * @param float         $boost
     * @param callable|null $callback
     * @return mixed
     */
    public function regexp(string $key = '', string $value = null, float $boost = 1.0, callable $callback = null)
    {
        //
    }

    /**
     * Create a new term query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html
     *
     * @param array         $terms
     * @param callable|null $callback
     * @return mixed
     */
    public function term(array $terms = [], callable $callback = null)
    {
        //
    }

    /**
     * Create a new terms query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html
     *
     * @param string        $key
     * @param array         $terms
     * @param callable|null $callback
     * @return mixed
     */
    public function terms(string $key = '', array $terms = [], callable $callback = null)
    {
        //
    }

    /**
     * Create a new wildcard query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-wildcard-query.html
     *
     * @param string        $key
     * @param string|null   $value
     * @param float         $boost
     * @param callable|null $callback
     * @return mixed
     */
    public function wildcard(string $key = '', string $value = null, float $boost = 1.0, callable $callback = null)
    {
        //
    }

    /**
     * Set the field to sort by.
     *
     * @param string $field
     * @return $this
     */
    public function sort(string $field)
    {
        return $this;
    }

    /**
     * Set the offset for the first result.
     *
     * @param int $from
     * @return $this
     */
    public function from(int $from)
    {
        return $this;
    }

    /**
     * Set the amount of results to be returned.
     *
     * @param int $size
     * @return $this
     */
    public function size(int $size)
    {
        return $this;
    }

    /**
     * Set the minimum should match value.
     *
     * @param int|string $minimumShouldMatch
     * @return $this
     */
    public function minimumShouldMatch($minimumShouldMatch)
    {
        return $this;
    }

    /**
     * Alias for "minimumShouldMatch".
     *
     * @param int|string $minimumShouldMatch
     * @return $this
     */
    public function setMinimumShouldMatch($minimumShouldMatch)
    {
        return $this;
    }

    /**
     * Set the boost value.
     *
     * @param float $boost
     * @return $this
     */
    public function boost($boost)
    {
        return $this;
    }

    /**
     * Alias for "boost".
     *
     * @param int|float $boost
     * @return $this
     */
    public function setBoost($boost)
    {
        return $this;
    }

    /**
     * Keep track of the scores when sorting results.
     *
     * @param bool $trackScores
     * @return Driver
     */
    public function trackScores(bool $trackScores = true)
    {
        return $this;
    }

    /**
     * Sets highlight arguments for the query.
     *
     * @param array $config
     * @return Driver
     */
    public function highlight(array $config = [])
    {
        return $this;
    }

    /**
     * Enables explain on the query.
     *
     * @param bool $explain
     * @return Driver
     */
    public function explain($explain = true)
    {
        return $this;
    }

    /**
     * Sets the fields to be returned by the search.
     *
     * @param array $fields
     * @return Driver
     */
    public function storedFields(array $fields)
    {
        return $this;
    }

    /**
     * Sets the fields not stored to be returned by the search.
     *
     * @param array $fields
     * @return Driver
     */
    public function fieldDataFields(array $fields)
    {
        return $this;
    }

    /**
     * Sets the fields not stored to be returned by the search.
     *
     * @param array $fields
     * @return Driver
     */
    public function scriptFields(array $fields)
    {
        return $this;
    }

    /**
     * Set the minimum score a document must match.
     *
     * @param float $score
     * @return Driver
     */
    public function minScore(float $score)
    {
        return $this;
    }

    /**
     * Sets the _source field to be returned with every hit.
     *
     * @param array|bool $params
     * @return Driver
     */
    public function source($params)
    {
        return $this;
    }

    /**
     * Set a query param.
     *
     * @param string $key
     * @param mixed  $value
     * @return Driver
     */
    public function setParam(string $key, $value)
    {
        return $this;
    }

    /**
     * Set all of the query parameters.
     *
     * @param array $params
     * @return Driver
     */
    public function setParams(array $params)
    {
        return $this;
    }

    /**
     * Check if a parameters has been set.
     *
     * @param string $key
     * @return bool
     */
    public function hasParam(string $key)
    {
        return true;
    }

    /**
     * Get all of the parameters.
     *
     * @return array
     */
    public function getParams()
    {
        return [];
    }

    /**
     * Add a new document to the provided type.
     *
     * @param string     $index
     * @param string|int $id
     * @param array      $data
     * @return mixed
     */
    public function add(string $index, $id, array $data)
    {
        return $this;
    }

    /**
     * Add multiple documents to the elasticsearch type. The data array must be a
     * multidimensional array with the key as the desired id and the value as
     * the data to be added to the document.
     *
     * @param string $index
     * @param array  $data
     * @return Driver
     */
    public function addMultiple(string $index, array $data)
    {
        return $this;
    }

    /**
     * Delete a document from the provided type.
     *
     * @param string     $index
     * @param string|int $id
     * @return Driver
     */
    public function delete(string $index, $id)
    {
        return $this;
    }
}