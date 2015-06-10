<?php namespace Michaeljennings\Laralastica; 

use Elastica\Client;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\Bool;
use Elastica\Query\Common;
use Elastica\Query\Fuzzy;
use Elastica\Query\Match;
use Elastica\Query\MatchAll;
use Elastica\Query\MultiMatch;
use Elastica\Query\Prefix;
use Elastica\Query\Range;
use Elastica\Query\Regexp;
use Elastica\Query\Term;
use Elastica\Query\Terms;
use Elastica\Query\Wildcard;
use Elastica\Type;
use Michaeljennings\Laralastica\Contracts\Builder as QueryBuilder;

class Builder implements QueryBuilder {

    /**
     * An array of queries to be searched.
     *
     * @var array
     */
    protected $query = [];

    /**
     * An instance of the elastica client.
     *
     * @var Client
     */
    protected $client;

    /**
     * The elasticsearch index being used.
     *
     * @var Index
     */
    protected $index;

    /**
     * The elasticsearch type being searched.
     *
     * @var string
     */
    protected $type;

    /**
     * The results of the query.
     *
     * @var mixed
     */
    protected $results;

    /**
     * Create the query builder.
     *
     * @param Client $client
     * @param Index $index
     * @param Type $type
     */
    public function __construct(Client $client, Index $index, Type $type)
    {
        $this->client = $client;
        $this->index = $index;
        $this->type = $type;
    }

    /**
     * Find all documents where the values are matched in the field. The type option
     * allows you to specify the type of match, can be either phrase or phrase_prefix.
     *
     * The phrase match analyzes the text and creates a phrase query out of the
     * analyzed text.
     *
     * The phrase prefix match is the same as phrase, except that it allows for
     * prefix matches on the last term in the text.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query.html
     *
     * @param string  $field  The field to search in the index
     * @param string  $query  The values to search for
     * @param string  $type   The match type
     * @param bool    $fuzzy  Set whether the match should be fuzzy
     * @return $this
     */
    public function match($field, $query, $type = 'phrase', $fuzzy = false)
    {
        $match = new Match();

        $match->setFieldQuery($field, $query);
        $match->setFieldType($field, $type);

        if ($fuzzy) {
            $match->setFieldFuzziness($field, 'AUTO');
        }

        $this->query[] = $match;

        return $this;
    }

    /**
     * Find all documents where the value is matched in the fields. The type option
     * allows you to specify the type of match, can be best_fields, most_fields,
     * cross_fields, phrase, phrase_prefix.
     *
     * best_fields finds documents which match any field, but uses the _score
     * from the best field.
     *
     * most_fields finds documents which match any field and combines the _score
     * from each field.
     *
     * cross_fields treats fields with the same analyzer as though they were
     * one big field. Looks for each word in any field.
     *
     * phrase runs a match_phrase query on each field and combines the _score
     * from each field.
     *
     * phrase_prefix runs a match_phrase_prefix query on each field and combines
     * the _score from each field.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-multi-match-query.html
     *
     * @param array $fields      The fields to search in
     * @param string $query      The string to search for
     * @param string $type       The match type
     * @param bool $fuzzy        Set whether the match should be fuzzy
     * @param float $tieBreaker  Can be between 0.0 and 1.0
     * @param string $operator   Can be 'and' or 'or'
     * @return $this
     */
    public function multiMatch(array $fields, $query, $type = 'phrase', $fuzzy = false, $tieBreaker = 0.0, $operator = 'and')
    {
        $match = new MultiMatch();

        $match->setFields($fields);
        $match->setQuery($query);
        $match->setType($type);

        if ($fuzzy) {
            $match->setFieldFuzziness($field, 'AUTO');
        }

        if ($type == 'best_fields') {
            $match->setTieBreaker($tieBreaker);
        }

        if ($type == 'cross_fields') {
            $match->setOperator($operator);
        }

        $this->query[] = $match;

        return $this;
    }

    /**
     * Find all documents where all possible matching terms are within the specified
     * fuzziness range. The fuzziness option can be 0, 1, 2 or AUTO, AUTO is
     * recommended.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-fuzzy-query.html
     *
     * @param string $field
     * @param string $value
     * @param string $fuzziness
     * @param int $prefixLength
     * @param int $maxExpansions
     * @return $this
     */
    public function fuzzy($field, $value, $fuzziness = 'AUTO', $prefixLength = 0, $maxExpansions = 50)
    {
        $fuzzy = new Fuzzy($field, $value);

        $fuzzy->setParam('fuzziness', $fuzziness);
        $fuzzy->setParam('prefix_length', $prefixLength);
        $fuzzy->setParam('max_expansions', $maxExpansions);

        $this->query[] = $fuzzy;

        return $this;
    }

    /**
     * Finds all documents matching the query but groups common words,
     * i.e. the, and runs them after the initial query for more efficiency.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-common-terms-query.html
     *
     * @param string $field
     * @param string $query
     * @param float $cutOff
     * @param int|bool $minimumMatch
     * @return $this
     */
    public function common($field, $query, $cutOff = 0.001, $minimumMatch = false)
    {
        $common = new Common($field, $query, $cutOff);

        if ($minimumMatch) {
            $common->setMinimumShouldMatch($minimumMatch);
        }

        $this->query[] = $common;

        return $this;
    }

    /**
     * A query which matches all documents.
     *
     * @return $this
     */
    public function matchAll()
    {
        $match = new MatchAll();

        $this->query[] = $match;

        return $this;
    }

    /**
     * Find all documents in a given range. The range is provided as an array with
     * at least either a 'lt' or 'lte' key and a 'gt' or 'gte' key.
     *
     * 'lt'  stands for less than
     * 'lte' for less than or equal to
     * 'gt'  for greater than
     * 'gte' for greater than or equal to
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html
     * 
     * @param string $field
     * @param array $range
     * @param bool $timeZone
     * @param bool $format
     * @return $this
     */
    public function range($field, array $range, $timeZone = false, $format = false)
    {
        $range = new Range($field, $range);

        if ($timeZone) {
            $range->setParam('time_zone', $timeZone);
        }

        if ($format) {
            $range->setParam('format', $format);
        }

        $this->query[] = $range;

        return $this;
    }

    /**
     * Find all documents that have fields containing terms with a specified
     * prefix.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-prefix-query.html
     *
     * @param string $field
     * @param string|array $prefix
     * @return $this
     */
    public function prefix($field, $prefix)
    {
        $query = new Prefix();

        if (is_string($prefix)) {
            $prefix = [$prefix];
        }

        $query->setPrefix($field, $prefix);

        $this->query[] = $query;

        return $this;
    }

    /**
     * Find all documents matching the provided regular expression.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-regexp-query.html
     *
     * @param string $field
     * @param string $regex
     * @return $this
     */
    public function regexp($field, $regex)
    {
        $regexp = new Regexp($field, $regex);

        $this->query[] = $regexp;

        return $this;
    }

    /**
     * Find a document matching an exact term.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html
     *
     * @param string $key
     * @param string $value
     * @param float $boost
     * @return $this
     */
    public function term($key, $value, $boost = 1.0)
    {
        $term = new Term();
        $term->setTerm($key, $value, $boost);

        $this->query[] = $term;

        return $this;
    }

    /**
     * Find any documents matching the provided terms, optionally you can set a
     * minimum amount of terms to match.
     *
     * @param string $key
     * @param array $terms
     * @param bool|int $minimumShouldMatch
     * @return $this
     */
    public function terms($key, array $terms, $minimumShouldMatch = false)
    {
        $query = new Terms($key, $terms);

        if ($minimumShouldMatch) {
            $query->setMinimumMatch($minimumShouldMatch);
        }

        $this->query[] = $query;

        return $this;
    }

    /**
     * Find a document matching a value containing a wildcard. Please note wildcard
     * queries can be very slow, to avoid this don't start a string with a wildcard.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-wildcard-query.html
     *
     * @param string $key
     * @param string $value
     * @param float $boost
     * @return $this
     */
    public function wildcard($key, $value, $boost = 1.0)
    {
        $query = new Wildcard($key, $value, $boost);

        $this->query[] = $query;

        return $this;
    }

    /**
     * Add a new document to the elasticsearch type.
     *
     * @param string|int $id
     * @param array $data
     * @return $this
     */
    public function add($id, array $data)
    {
        $document = new Document($id, $data);
        $this->type->addDocument($document);

        return $this;
    }

    /**
     * Add multiple documents to the elasticsearch type.
     *
     * @param array $data
     * @return $this
     */
    public function addMultiple(array $data)
    {
        $documents = [];

        foreach ($data as $id => $values) {
            $documents[] = new Document($id, $values);
        }

        $this->type->addDocuments($documents);

        return $this;
    }

    /**
     * Delete a document by its id.
     *
     * @param string|int $id
     * @return \Elastica\Response
     */
    public function delete($id)
    {
        return $this->type->deleteById($id);
    }

    /**
     * Run the queries on the elastic search type and return the results.
     *
     * @return mixed
     */
    public function results()
    {
        if ( ! empty($this->query)) {
            $container = new Bool();

            foreach ($this->query as $query) {
                $container->addMust($query);
            }

            $query = new Query($container);
            $query->addSort('_score');
        } else {
            $query = new Query();
        }

        // Retrieve the result set
        $resultSet = $this->type->search($query, 1000);

        $this->results = $resultSet->getResults();

        return $this;
    }

    /**
     * Get the results of the query.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Check if the query has been run.
     *
     * @return bool
     */
    public function hasResults()
    {
        return isset($this->results);
    }

    /**
     * Get the queries to be run.
     *
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

}