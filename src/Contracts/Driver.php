<?php

namespace Michaeljennings\Laralastica\Contracts;

use Elastica\Document;
use Elastica\Query\AbstractQuery;
use Elastica\Query\Common;
use Elastica\Query\Fuzzy;
use Elastica\Query\MatchAll;
use Michaeljennings\Laralastica\Drivers\ElasticaDriver;

interface Driver
{
    /**
     * Execute the provided queries.
     *
     * @param string|array $types
     * @param array        $queries
     * @return \Elastica\ResultSet
     */
    public function get($types, array $queries);

    /**
     * Create a common query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-common-terms-query.html
     *
     * @param string        $field
     * @param string        $query
     * @param float         $cutoffFrequency
     * @param callable|null $callback
     * @return Common
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
     * @return Fuzzy
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
     * @return AbstractQuery
     */
    public function match($field = null, $value = null, callable $callback = null);

    /**
     * Create a match all query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-all-query.html
     *
     * @return MatchAll
     */
    public function matchAll();

    /**
     * Create a query string query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html
     *
     * @param string        $query
     * @param callable|null $callback
     * @return AbstractQuery
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
     * @return AbstractQuery
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
     * @return AbstractQuery
     */
    public function regexp($key = '', $value = null, $boost = 1.0, callable $callback = null);

    /**
     * Create a new term query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html
     *
     * @param array         $terms
     * @param callable|null $callback
     * @return AbstractQuery
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
     * @return AbstractQuery
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
     * @return AbstractQuery
     */
    public function wildcard($key = '', $value = null, $boost = 1.0, callable $callback = null);

    /**
     * Add a new document to the provided type.
     *
     * @param string     $type
     * @param string|int $id
     * @param array      $data
     * @return Document
     */
    public function add($type, $id, array $data);

    /**
     * Add multiple documents to the elasticsearch type. The data array must be a
     * multidimensional array with the key as the desired id and the value as
     * the data to be added to the document.
     *
     * @param string $type
     * @param array  $data
     * @return $this
     */
    public function addMultiple($type, array $data);

    /**
     * Delete a document from the provided type.
     *
     * @param string     $type
     * @param string|int $id
     * @return $this
     */
    public function delete($type, $id);
}