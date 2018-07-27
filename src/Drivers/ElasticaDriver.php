<?php

namespace Michaeljennings\Laralastica\Drivers;

use Elastica\Client;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query as ElasticaQuery;
use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Common;
use Elastica\Query\Exists;
use Elastica\Query\Fuzzy;
use Elastica\Query\Match;
use Elastica\Query\MatchAll;
use Elastica\Query\MatchPhrase;
use Elastica\Query\MatchPhrasePrefix;
use Elastica\Query\MultiMatch;
use Elastica\Query\QueryString;
use Elastica\Query\Range;
use Elastica\Query\Regexp;
use Elastica\Query\Term;
use Elastica\Query\Terms;
use Elastica\Query\Wildcard;
use Elastica\Result;
use Elastica\ResultSet;
use Elastica\Search;
use Michaeljennings\Laralastica\Contracts\Builder;
use Michaeljennings\Laralastica\Contracts\Driver;
use Michaeljennings\Laralastica\Contracts\Filter;
use Michaeljennings\Laralastica\Contracts\Query;
use Michaeljennings\Laralastica\LengthAwarePaginator;
use Michaeljennings\Laralastica\ResultCollection;

class ElasticaDriver implements Driver
{
    /**
     * The elastica client.
     *
     * @var Client
     */
    protected $client;

    /**
     * The elastica index.
     *
     * @var Index
     */
    protected $index;

    /**
     * The laralastica config.
     *
     * @var array
     */
    protected $config;

    public function __construct(Client $client, Index $index, array $config)
    {
        $this->client = $client;
        $this->index = $index;
        $this->config = $config;
    }

    /**
     * Execute the provided queries.
     *
     * @param string|array $types
     * @param array        $queries
     * @return ResultCollection
     */
    public function get($types, array $queries)
    {
        $search = $this->newSearch($types);
        $query = $this->newQuery($queries);

        $query->setSize($this->config['size']);
        $search->setQuery($query);

        return $this->newResultCollection($search->search());
    }

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
    public function paginate($types, array $queries, $page, $perPage, $offset)
    {
        $search = $this->newSearch($types);
        $query = $this->newQuery($queries);

        $query->setSize($perPage);
        $query->setFrom($offset);

        $search->setQuery($query);

        $results = $search->search();

        $paginator = new LengthAwarePaginator($this->hydrateResults($results), $results->getTotalHits(), $perPage,
            $page);

        return $paginator->setQueryStats($results->getTotalHits(), $results->getMaxScore(), $results->getTotalTime());
    }

    /**
     * Add a new document to the provided type.
     *
     * @param string     $type
     * @param string|int $id
     * @param array      $data
     * @return $this
     */
    public function add($type, $id, array $data)
    {
        $type = $this->getType($type);
        $document = new Document($id, $data);
        $type->addDocument($document);

        $this->refreshIndex();

        return $this;
    }

    /**
     * Add multiple documents to the elasticsearch type. The data array must be a
     * multidimensional array with the key as the desired id and the value as
     * the data to be added to the document.
     *
     * @param string $type
     * @param array  $data
     * @return $this
     */
    public function addMultiple($type, array $data)
    {
        $type = $this->getType($type);
        $documents = [];

        foreach ($data as $id => $values) {
            $documents[] = new Document($id, $values);
        }

        $type->addDocuments($documents);

        $this->refreshIndex();

        return $this;
    }

    /**
     * Delete a document from the provided type.
     *
     * @param string     $type
     * @param string|int $id
     * @return $this
     */
    public function delete($type, $id)
    {
        $type = $this->getType($type);
        $type->deleteById($id);

        $this->refreshIndex();

        return $this;
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
     * @return Common
     */
    public function common($field, $query, $cutoffFrequency, callable $callback = null)
    {
        $query = new Common($field, $query, $cutoffFrequency);

        return $this->returnQuery($query, $callback);
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
    public function exists($key, callable $callback = null)
    {
        $query = new Exists($key);

        return $this->returnQuery($query, $callback);
    }

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
    public function fuzzy($field, $value, callable $callback = null)
    {
        $query = new Fuzzy($field, $value);

        return $this->returnQuery($query, $callback);
    }

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
    public function match($field = null, $value = null, callable $callback = null)
    {
        $query = new Match();

        $query->setFieldQuery($field, $value);

        return $this->returnQuery($query, $callback);
    }

    /**
     * Create a new match phrase query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query-phrase.html
     *
     * @param string|null   $field
     * @param string|null   $value
     * @param callable|null $callback
     * @return AbstractQuery
     */
    public function matchPhrase($field = null, $value = null, callable $callback = null)
    {
        $query = new MatchPhrase();

        $query->setFieldQuery($field, $value);

        return $this->returnQuery($query, $callback);
    }

    /**
     * Create a new match phrase prefix query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query-phrase-prefix.html
     *
     * @param string|null   $field
     * @param string|null   $value
     * @param callable|null $callback
     * @return AbstractQuery
     */
    public function matchPhrasePrefix($field = null, $value = null, callable $callback = null)
    {
        $query = new MatchPhrasePrefix();

        $query->setFieldQuery($field, $value);

        return $this->returnQuery($query, $callback);
    }

    /**
     * Create a match all query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-all-query.html
     *
     * @return MatchAll
     */
    public function matchAll()
    {
        return new MatchAll();
    }

    /**
     * Create a new multi match query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/6.2/query-dsl-multi-match-query.html
     *
     * @param array|null    $fields
     * @param string|null   $value
     * @param callable|null $callback
     * @return AbstractQuery
     */
    public function multiMatch(array $fields = null, $value = null, callable $callback = null)
    {
        $query = new MultiMatch();

        if ($fields) {
            $query->setFields($fields);
        }

        if ($value) {
            $query->setQuery($value);
        }

        return $this->returnQuery($query, $callback);
    }

    /**
     * Create a query string query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html
     *
     * @param string        $query
     * @param callable|null $callback
     * @return AbstractQuery
     */
    public function queryString($query = '', callable $callback = null)
    {
        $query = new QueryString($query);

        return $this->returnQuery($query, $callback);
    }

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
    public function range($fieldName = null, $args = [], callable $callback = null)
    {
        $query = new Range($fieldName, $args);

        return $this->returnQuery($query, $callback);
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
     * @return AbstractQuery
     */
    public function regexp($key = '', $value = null, $boost = 1.0, callable $callback = null)
    {
        $query = new Regexp($key, $value, $boost);

        return $this->returnQuery($query, $callback);
    }

    /**
     * Create a new term query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html
     *
     * @param array         $terms
     * @param callable|null $callback
     * @return AbstractQuery
     */
    public function term(array $terms = [], callable $callback = null)
    {
        $query = new Term($terms);

        return $this->returnQuery($query, $callback);
    }

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
    public function terms($key = '', array $terms = [], callable $callback = null)
    {
        $query = new Terms($key, $terms);

        return $this->returnQuery($query, $callback);
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
     * @return AbstractQuery
     */
    public function wildcard($key = '', $value = null, $boost = 1.0, callable $callback = null)
    {
        $query = new Wildcard($key, $value, $boost);

        return $this->returnQuery($query, $callback);
    }

    /**
     * Create a new search.
     *
     * @param string|array $types
     * @return Search
     */
    protected function newSearch($types)
    {
        if ( ! is_array($types)) {
            $types = func_get_args();
        }

        $search = new Search($this->client);

        $search->addIndex($this->index);
        $search->addTypes($types);

        return $search;
    }

    /**
     * Create a new elastica query from an array of queries.
     *
     * @param array $queries
     * @return Query
     */
    protected function newQuery(array $queries)
    {
        if ( ! empty($queries)) {
            $container = $this->addQueries(new BoolQuery(), $queries);

            $query = new ElasticaQuery($container);
            $query->addSort('_score');
        } else {
            $query = new ElasticaQuery();
        }

        return $query;
    }

    /**
     * Add the queries to the container.
     *
     * @param BoolQuery $container
     * @param array     $queries
     * @return BoolQuery
     */
    protected function addQueries(BoolQuery $container, array $queries)
    {
        foreach ($queries as $query) {
            if ($query instanceof Filter) {
                $container = $this->addFilterToContainer($query, $container);
            } else {
                $container = $this->addQueryToContainer($query, $container);
            }
        }

        return $container;
    }

    /**
     * Set the type of match for the query and then add it to the bool container.
     *
     * @param Query     $query
     * @param BoolQuery $container
     * @return BoolQuery
     */
    protected function addQueryToContainer(Query $query, BoolQuery $container)
    {
        switch ($query->getType()) {
            case "must":
                $container->addMust($query->getQuery());
                break;
            case "should":
                $container->addShould($query->getQuery());
                break;
            case "must_not":
                $container->addMustNot($query->getQuery());
                break;
        }

        return $container;
    }

    /**
     * Add the filter to the query container.
     *
     * @param Filter    $filter
     * @param BoolQuery $container
     * @return BoolQuery
     */
    protected function addFilterToContainer(Filter $filter, BoolQuery $container)
    {
        $filterQuery = new BoolQuery();
        $filter = $filter->getFilter();

        if ($filter instanceof Builder) {
            foreach ($filter->getQueries() as $query) {
                $this->addQueryToContainer($query, $filterQuery);
            }
        } else {
            $this->addQueryToContainer($filter, $filterQuery);
        }

        $container->addFilter($filterQuery);

        return $container;
    }

    /**
     * Create a new result collection.
     *
     * @param ResultSet $results
     * @return ResultCollection
     */
    protected function newResultCollection(ResultSet $results)
    {
        $collection = new ResultCollection($this->hydrateResults($results));

        $collection->setQueryStats($results->getTotalHits(), $results->getMaxScore(), $results->getTotalTime());

        return $collection;
    }

    /**
     * Parse the result set to our result entity.
     *
     * @param ResultSet $results
     * @return \Michaeljennings\Laralastica\Result[]
     */
    protected function hydrateResults(ResultSet $results)
    {
        $items = [];

        foreach ($results as $result) {
            $items[] = $this->newResult($result);
        }

        return $items;
    }

    /**
     * Create a new result.
     *
     * @param Result $result
     * @return \Michaeljennings\Laralastica\Result
     */
    protected function newResult(Result $result)
    {
        return new \Michaeljennings\Laralastica\Result(
            $result->getData(),
            $result,
            $result->getIndex(),
            $result->getType(),
            $result->getScore()
        );
    }

    /**
     * Get an elasticsearch type from its index.
     *
     * @param string $type
     * @return \Elastica\Type
     */
    protected function getType($type)
    {
        return $this->index->getType($type);
    }

    /**
     * Refreshes the elasticsearch index, this should be run after adding
     * or deleting documents.
     *
     * @return \Elastica\Response
     */
    protected function refreshIndex()
    {
        return $this->index->refresh();
    }

    /**
     * If the callback is set run it on the query and then return the query.
     *
     * @param AbstractQuery $query
     * @param callable|null $callback
     * @return AbstractQuery
     */
    protected function returnQuery(AbstractQuery $query, callable $callback = null)
    {
        if ($callback) {
            $callback($query);
        }

        return $query;
    }
}