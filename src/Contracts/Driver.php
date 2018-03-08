<?php

namespace Michaeljennings\Laralastica\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Michaeljennings\Laralastica\ResultCollection;

interface Driver
{
    /**
     * Execute the provided queries.
     *
     * @param string|array $types
     * @param array        $queries
     * @return ResultCollection
     */
    public function get($types, array $queries);

    /**
     * Execute the query and return a paginated list of results.
     *
     * @param string|array $types
     * @param array        $queries
     * @param int          $page
     * @param int          $perPage
     * @param int          $offset
     * @return LengthAwarePaginator
     */
    public function paginate($types, array $queries, $page, $perPage, $offset);

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
    public function common($field, $query, $cutoffFrequency, callable $callback = null);

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
    public function fuzzy($field, $value, callable $callback = null);

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
    public function match($field = null, $value = null, callable $callback = null);

    /**
     * Create a new match phrase query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query-phrase.html
     *
     * @param string|null   $field
     * @param string|null   $value
     * @param callable|null $callback
     * @return mixed
     */
    public function matchPhrase($field = null, $value = null, callable $callback = null);

    /**
     * Create a new match phrase prefix query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query-phrase-prefix.html
     *
     * @param string|null   $field
     * @param string|null   $value
     * @param callable|null $callback
     * @return mixed
     */
    public function matchPhrasePrefix($field = null, $value = null, callable $callback = null);

    /**
     * Create a match all query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-all-query.html
     *
     * @return mixed
     */
    public function matchAll();

    /**
     * Create a new multi match query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/6.2/query-dsl-multi-match-query.html
     *
     * @param array|null    $fields
     * @param string|null   $value
     * @param callable|null $callback
     * @return mixed
     */
    public function multiMatch(array $fields = null, $value = null, callable $callback = null);

    /**
     * Create a query string query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html
     *
     * @param string        $query
     * @param callable|null $callback
     * @return mixed
     */
    public function queryString($query = '', callable $callback = null);

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
    public function range($fieldName = null, $args = [], callable $callback = null);

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
    public function regexp($key = '', $value = null, $boost = 1.0, callable $callback = null);

    /**
     * Create a new term query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html
     *
     * @param array         $terms
     * @param callable|null $callback
     * @return mixed
     */
    public function term(array $terms = [], callable $callback = null);

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
    public function terms($key = '', array $terms = [], callable $callback = null);

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
    public function wildcard($key = '', $value = null, $boost = 1.0, callable $callback = null);

    /**
     * Add a new document to the provided type.
     *
     * @param string     $type
     * @param string|int $id
     * @param array      $data
     * @return mixed
     */
    public function add($type, $id, array $data);

    /**
     * Add multiple documents to the elasticsearch type. The data array must be a
     * multidimensional array with the key as the desired id and the value as
     * the data to be added to the document.
     *
     * @param string $type
     * @param array  $data
     * @return Driver
     */
    public function addMultiple($type, array $data);

    /**
     * Delete a document from the provided type.
     *
     * @param string     $type
     * @param string|int $id
     * @return Driver
     */
    public function delete($type, $id);
}