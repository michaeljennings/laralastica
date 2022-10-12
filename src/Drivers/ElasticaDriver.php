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
use Elastica\Query\MatchQuery;
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
use Michaeljennings\Laralastica\IndexPrefixer;
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
     * The index prefixing service.
     *
     * @var IndexPrefixer
     */
    protected $indexPrefixer;

    /**
     * The laralastica config.
     *
     * @var array
     */
    protected $config;

    /**
     * Set the minimum fields that should be matched by a result.
     *
     * @var int|string
     */
    protected $minimumShouldMatch;

    /**
     * Set the amount to boost results by.
     *
     * @var float
     */
    protected $boost;

    /**
     * Set the default field to sort results by.
     *
     * @var array|string
     */
    protected $sort = ['_score' => 'desc'];

    /**
     * The query parameters.
     *
     * @var array
     */
    protected $params = [];

    public function __construct(Client $client, IndexPrefixer $indexPrefixer, array $config)
    {
        $this->client = $client;
        $this->indexPrefixer = $indexPrefixer;
        $this->config = $config;
    }

    /**
     * Execute the provided queries.
     *
     * @param string|array $indices
     * @param array        $queries
     * @return ResultCollection
     */
    public function get($indices, array $queries)
    {
        $search = $this->newSearch($indices);
        $query = $this->newQuery($queries);

        $this->setQueryParams($query);

        $search->setQuery($query);

        return $this->newResultCollection($search->search());
    }

    /**
     * Execute the query and return a paginated list of results.
     *
     * @param string|array $indices
     * @param array        $queries
     * @param int          $page
     * @param int          $perPage
     * @param int          $offset
     * @return LengthAwarePaginator
     */
    public function paginate($indices, array $queries, $page, $perPage, $offset)
    {
        $search = $this->newSearch($indices);
        $query = $this->newQuery($queries);

        $this->setQueryParams($query, $perPage, $offset);

        $search->setQuery($query);

        $results = $search->search();

        $paginator = new LengthAwarePaginator(
            $this->hydrateResults($results),
            $results->getTotalHits(),
            $perPage,
            $page
        );

        return $paginator->setQueryStats($results->getTotalHits(), $results->getMaxScore(), $results->getTotalTime());
    }

    /**
     * Set the query parameters.
     *
     * @param ElasticaQuery $query
     * @param int|null      $size
     * @param int|null      $from
     */
    protected function setQueryParams(ElasticaQuery $query, $size = null, $from = null)
    {
        if ($size) {
            $this->size($size);
        }

        if ($from) {
            $this->from($from);
        }

        if ( ! $this->hasParam('size')) {
            $this->size($this->config['size']);
        }

        if ( ! $this->hasParam('sort')) {
            $this->sort($this->sort);
        }

        foreach ($this->params as $key => $value) {
            $query->setParam($key, $value);
        }

        // $query->setParams($this->params);

        $this->params = [];
    }

    /**
     * Add a new document to the provided index.
     *
     * @param string     $index
     * @param string|int $id
     * @param array      $data
     * @return $this
     */
    public function add(string $index, $id, array $data)
    {
        $index = $this->getIndexName($index);

        $document = $this->newDocument($index, $id, $data);

        $this->addDocumentsToIndex($index, [$document]);

        return $this;
    }

    /**
     * Add multiple documents to the elasticsearch index. The data must be an
     * associative array with the key as the desired id and the value as the
     * data to be added to the document.
     *
     * @param string $index
     * @param array  $data
     * @return $this
     */
    public function addMultiple(string $index, array $data)
    {
        $index = $this->getIndexName($index);
        $documents = [];

        foreach ($data as $id => $values) {
            $documents[] = $this->newDocument($index, $id, $values);
        }

        $this->addDocumentsToIndex($index, $documents);

        return $this;
    }

    /**
     * Add the documents to the index.
     *
     * @param string           $index
     * @param array|Document[] $documents
     */
    protected function addDocumentsToIndex(string $index, array $documents)
    {
        // Elasticsearch types are being deprecated so you can only have
        // one type per index in elasticsearch 6, types will be removed
        // completely in elasticsearch 7. However in elasticsearch 6 you
        // cannot create a document without a type so here we are setting
        // the type to be same as the index.
        //
        // See: https://www.elastic.co/guide/en/elasticsearch/reference/current/removal-of-types.html
        foreach ($documents as $document) {
            $document->setIndex($index);
        }

        $index = $this->getIndex($this->getIndexName($index));

        $index->addDocuments($documents);

        $index->refresh();
    }

    /**
     * Delete a document from the provided type.
     *
     * @param string     $index
     * @param string|int $id
     * @return $this
     */
    public function delete(string $index, $id)
    {
        $document = $this->newDocument($this->getIndexName($index), $id);
        $index = $this->getIndex($index);

        $index->deleteDocuments([$document]);

        $index->refresh();

        return $this;
    }

    /**
     * Create a bool query.
     *
     * @param array $queries
     * @return BoolQuery
     */
    public function bool(array $queries = [])
    {
        $boolQuery = $this->addQueries(new BoolQuery(), $queries);

        if (isset($this->minimumShouldMatch)) {
            $boolQuery->setMinimumShouldMatch($this->minimumShouldMatch);
        }

        if (isset($this->boost)) {
            $boolQuery->setBoost($this->boost);
        }

        return $boolQuery;
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
    public function common(string $field, string $query, float $cutoffFrequency, callable $callback = null)
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
    public function exists(string $key, callable $callback = null)
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
    public function fuzzy(string $field, string $value, callable $callback = null)
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
    public function matchQuery(string $field = null, string $value = null, callable $callback = null)
    {
        $query = new MatchQuery();

        if (!is_null($field) && !is_null($value)) {
            $query->setFieldQuery($field, $value);
        }

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
    public function matchPhrase(string $field = null, string $value = null, callable $callback = null)
    {
        $query = new MatchPhrase();

        if (!is_null($field) && !is_null($value)) {
            $query->setFieldQuery($field, $value);
        }

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
    public function matchPhrasePrefix(string $field = null, string $value = null, callable $callback = null)
    {
        $query = new MatchPhrasePrefix();

        if (!is_null($field) && !is_null($value)) {
            $query->setFieldQuery($field, $value);
        }

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
    public function multiMatch(array $fields = null, string $value = null, callable $callback = null)
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
    public function queryString(string $query = '', callable $callback = null)
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
    public function range(string $fieldName = null, array $args = [], callable $callback = null)
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
    public function regexp(string $key = '', string $value = null, float $boost = 1.0, callable $callback = null)
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
    public function terms(string $key = '', array $terms = [], callable $callback = null)
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
    public function wildcard(string $key = '', string $value = null, float $boost = 1.0, callable $callback = null)
    {
        $query = new Wildcard($key, $value, $boost);

        return $this->returnQuery($query, $callback);
    }

    /**
     * Set the offset for the first result.
     *
     * @param int $from
     * @return $this
     */
    public function from(int $from)
    {
        return $this->setParam('from', $from);
    }

    /**
     * Set the amount of results to be returned.
     *
     * @param int $size
     * @return $this
     */
    public function size(int $size)
    {
        return $this->setParam('size', $size);
    }

    /**
     * Set the field to sort by.
     *
     * @param array|string $field
     * @param string       $order
     * @return $this
     */
    public function sort($field, string $order = 'asc')
    {
        if (is_array($field)) {
            return $this->setParam('sort', $field);
        } else {
            return $this->setParam('sort', [$field => $order]);
        }
    }

    /**
     * Keep track of the scores when sorting results.
     *
     * @param bool $trackScores
     * @return $this
     */
    public function trackScores(bool $trackScores = true)
    {
        return $this->setParam('track_scores', $trackScores);
    }

    /**
     * Sets highlight arguments for the query.
     *
     * @param array $config
     * @return $this
     */
    public function highlight(array $config = [])
    {
        return $this->setParam('highlight', $config);
    }

    /**
     * Enables explain on the query.
     *
     * @param bool $explain
     * @return $this
     */
    public function explain($explain = true)
    {
        return $this->setParam('explain', $explain);
    }

    /**
     * Sets the fields to be returned by the search.
     *
     * @param array $fields
     * @return ElasticaDriver
     */
    public function storedFields(array $fields)
    {
        return $this->setParam('stored_fields', $fields);
    }

    /**
     * Sets the fields not stored to be returned by the search.
     *
     * @param array $fields
     * @return ElasticaDriver
     */
    public function fieldDataFields(array $fields)
    {
        return $this->setParam('docvalue_fields', $fields);
    }

    /**
     * Sets the fields not stored to be returned by the search.
     *
     * @param array $fields
     * @return ElasticaDriver
     */
    public function scriptFields(array $fields)
    {
        return $this->setParam('script_fields', $fields);
    }

    /**
     * Set the minimum score a document must match.
     *
     * @param float $score
     * @return $this
     */
    public function minScore(float $score)
    {
        return $this->setParam('min_score', $score);
    }

    /**
     * Sets the _source field to be returned with every hit.
     *
     * @param array|bool $params
     * @return $this
     */
    public function source($params)
    {
        return $this->setParam('_source', $params);
    }

    /**
     * Set the minimum should match value.
     *
     * @param int|string $minimumShouldMatch
     * @return $this
     */
    public function minimumShouldMatch($minimumShouldMatch)
    {
        $this->minimumShouldMatch = $minimumShouldMatch;

        return $this;
    }

    /**
     * Alias for "minimumShouldMatch".
     *
     * @param int|string $minimumShouldMatch
     * @return ElasticaDriver
     */
    public function setMinimumShouldMatch($minimumShouldMatch)
    {
        return $this->minimumShouldMatch($minimumShouldMatch);
    }

    /**
     * Set the boost value.
     *
     * @param float $boost
     * @return $this
     */
    public function boost($boost)
    {
        $this->boost = $boost;

        return $this;
    }

    /**
     * Alias for "boost".
     *
     * @param int|float $boost
     * @return ElasticaDriver
     */
    public function setBoost($boost)
    {
        return $this->boost($boost);
    }

    /**
     * Set a query parameter.
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setParam(string $key, $value)
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * Set all of the query parameters.
     *
     * @param array $params
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->params = $params;

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
        return array_key_exists($key, $this->params);
    }

    /**
     * Get all of the parameters.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Create a new search.
     *
     * @param string|array $indices
     * @return Search
     */
    protected function newSearch($indices)
    {
        if ( ! is_array($indices)) {
            $indices = func_get_args();
        }

        foreach ($indices as $key => $index) {
            $indices[$key] = $this->getIndexName($index);
        }

        $search = new Search($this->client);

        $search->addIndices($indices);

        return $search;
    }

    /**
     * Create a new elastica query from an array of queries.
     *
     * @param array $queries
     * @return ElasticaQuery
     */
    protected function newQuery(array $queries)
    {
        if ( ! empty($queries)) {
            $query = new ElasticaQuery(
                $this->bool($queries)
            );
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
        $filter = $filter->getFilter();

        if ($filter instanceof Builder) {
            $filterQuery = new BoolQuery();

            foreach ($filter->getQueries() as $query) {
                $this->addQueryToContainer($query, $filterQuery);
            }

            $container->addFilter($filterQuery);
        } else {
            $container->addFilter($filter);
        }

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
     * Create a new document.
     *
     * @param string     $type
     * @param int|string $id
     * @param array      $values
     * @return Document
     */
    protected function newDocument(string $type, $id, array $values = [])
    {
        return new Document($id, $values, $type);
    }

    /**
     * Get the index from the client.
     *
     * @param string $index
     * @return Index
     */
    protected function getIndex(string $index)
    {
        return $this->client->getIndex($this->getIndexName($index));
    }

    /**
     * Get the index name including the prefix if required.
     *
     * @param string $index
     * @return string
     */
    protected function getIndexName(string $index)
    {
        return $this->indexPrefixer->prefix($index);
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
