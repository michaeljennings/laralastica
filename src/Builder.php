<?php namespace Michaeljennings\Laralastica; 

use Elastica\Client;
use Elastica\Query\Bool;
use Elastica\Query\Common;
use Elastica\Query\Fuzzy;
use Elastica\Query\Match;
use Elastica\Query\MatchAll;
use Elastica\Query\MultiMatch;
use Elastica\Query\Range;

class Builder {

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

    protected $index;

    protected $type;

    public function __construct(Client $client, $index, $type)
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
     * @param string        $field  The field to search in the index
     * @param string|array  $values The values to search for
     * @param string        $type   The match type
     * @param bool          $fuzzy  Set whether the match should be fuzzy
     * @return $this
     */
    public function match($field, $values, $type = 'phrase', $fuzzy = false)
    {
        $match = new Match();

        if (is_string($values)) {
            $values = [$values];
        }

        $match->setField($field, $values);
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
     * Run the queries on the elastic search type and return the results.
     *
     * @return mixed
     */
    public function results()
    {
        if ( ! empty($this->query)) {
            $query = new Elastica\Query($this->query);
            $query->addSort('_score');
        } else {
            $query = new Elastica\Query();
        }

        // Retrieve the result set
        $resultSet = $this->type->search($query, 1000);

        return $resultSet->getResults();
    }

}