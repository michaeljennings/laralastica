<?php namespace Michaeljennings\Laralastica\Contracts;

use Elastica\Query\Prefix;
use Elastica\Query\Regexp;

interface Builder {

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
    public function match($field, $values, $type = 'phrase', $fuzzy = false);

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
    public function multiMatch(array $fields, $query, $type = 'phrase', $fuzzy = false, $tieBreaker = 0.0, $operator = 'and');

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
    public function fuzzy($field, $value, $fuzziness = 'AUTO', $prefixLength = 0, $maxExpansions = 50);

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
    public function common($field, $query, $cutOff = 0.001, $minimumMatch = false);

    /**
     * A query which matches all documents.
     *
     * @return $this
     */
    public function matchAll();

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
    public function range($field, array $range, $timeZone = false, $format = false);

    /**
     * Add a new document to the elasticsearch type.
     *
     * @param string|int $id
     * @param array $data
     * @return $this
     */
    public function add($id, array $data);

    /**
     * Add multiple documents to the elasticsearch type.
     *
     * @param array $data
     * @return $this
     */
    public function addMultiple(array $data);

    /**
     * Delete a document by its id.
     *
     * @param string|int $id
     * @return \Elastica\Response
     */
    public function delete($id);

    /**
     * Run the queries on the elastic search type and return the results.
     *
     * @return mixed
     */
    public function results();

    /**
     * Get the results of the query.
     *
     * @return mixed
     */
    public function getResults();

    /**
     * Check if the query has been run.
     *
     * @return bool
     */
    public function hasResults();

    /**
     * Find all documents that have fields containing terms with a specified
     * prefix.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-prefix-query.html
     *
     * @param string $field
     * @param string|array $prefix
     * @return \Michaeljennings\Laralastica\Builder
     */
    public function prefix($field, $prefix);

    /**
     * Find all documents matching the provided regular expression.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-regexp-query.html
     *
     * @param string $field
     * @param string $regex
     * @return \Michaeljennings\Laralastica\Builder
     */
    public function regexp($field, $regex);

}