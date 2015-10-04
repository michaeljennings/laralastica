<?php namespace Michaeljennings\Laralastica; 

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
use Elastica\Query\AbstractQuery;
use Michaeljennings\Laralastica\Contracts\Builder as QueryBuilder;
use Michaeljennings\Laralastica\Query;

class Builder implements QueryBuilder {

    /**
     * An array of queries to be searched.
     *
     * @var array
     */
    protected $query = [];

    /**
     * The results of the query.
     *
     * @var mixed
     */
    protected $results;

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
     * @return Query
     */
    public function match($field, $query, $type = 'phrase', $fuzzy = false)
    {
        $match = new Match();

        $match->setFieldQuery($field, $query);
        $match->setFieldType($field, $type);

        if ($fuzzy) {
            $match->setFieldFuzziness($field, 'AUTO');
        }

        $query = $this->newQuery($match);
        $this->query[] = $query;

        return $query;
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
     * @return Query
     */
    public function multiMatch(array $fields, $query, $type = 'phrase', $fuzzy = false, $tieBreaker = 0.0, $operator = 'and')
    {
        $match = new MultiMatch();

        $match->setFields($fields);
        $match->setQuery($query);
        $match->setType($type);

        if ($fuzzy) {
            $match->setFuzziness('AUTO');
        }

        if ($type == 'best_fields') {
            $match->setTieBreaker($tieBreaker);
        }

        if ($type == 'cross_fields') {
            $match->setOperator($operator);
        }

        $query = $this->newQuery($match);
        $this->query[] = $query;

        return $query;
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
     * @return Query
     */
    public function common($field, $query, $cutOff = 0.001, $minimumMatch = false)
    {
        $common = new Common($field, $query, $cutOff);

        if ($minimumMatch) {
            $common->setMinimumShouldMatch($minimumMatch);
        }

        $query = $this->newQuery($common);
        $this->query[] = $query;

        return $query;
    }

    /**
     * A query which matches all documents.
     *
     * @return Query
     */
    public function matchAll()
    {
        $match = new MatchAll();

        $query = $this->newQuery($match);
        $this->query[] = $query;

        return $query;
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
     * @return Query
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

        $query = $this->newQuery($range);
        $this->query[] = $query;

        return $query;
    }

    /**
     * Find all documents matching the provided regular expression.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-regexp-query.html
     *
     * @param string $field
     * @param string $regex
     * @return Query
     */
    public function regexp($field, $regex)
    {
        $regexp = new Regexp($field, $regex);

        $query = $this->newQuery($regexp);
        $this->query[] = $query;

        return $query;
    }

    /**
     * Find a document matching an exact term.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html
     *
     * @param string $key
     * @param string $value
     * @param float $boost
     * @return Query
     */
    public function term($key, $value, $boost = 1.0)
    {
        $term = new Term();
        $term->setTerm($key, $value, $boost);

        $query = $this->newQuery($term);
        $this->query[] = $query;

        return $query;
    }

    /**
     * Find any documents matching the provided terms, optionally you can set a
     * minimum amount of terms to match.
     *
     * @param string $key
     * @param array $terms
     * @param bool|int $minimumShouldMatch
     * @return Query
     */
    public function terms($key, array $terms, $minimumShouldMatch = false)
    {
        $query = new Terms($key, $terms);

        if ($minimumShouldMatch) {
            $query->setMinimumMatch($minimumShouldMatch);
        }

        $query = $this->newQuery($query);
        $this->query[] = $query;

        return $query;
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
     * @return Query
     */
    public function wildcard($key, $value, $boost = 1.0)
    {
        $query = new Wildcard($key, $value, $boost);

        $query = $this->newQuery($query);
        $this->query[] = $query;

        return $query;
    }
    
    /**
     * Add an abstract elastica query to array
     * 
     * @param AbstractQuery $query
     * @return Query
     */
    public function addQuery(AbstractQuery $query)
    {
        $query = $this->newQuery($query);
        $this->query[] = $query;

        return $query;
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

    /**
     * Create a new query wrapper.
     *
     * @param mixed $query
     * @return Query
     */
    protected function newQuery($query)
    {
        return new Query($query);
    }

}
