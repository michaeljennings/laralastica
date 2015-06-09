<?php namespace Michaeljennings\Laralastica\Contracts;

interface Search {

    /**
     * Add a wildcard search to the query collection
     *
     * @param  string $field    The field to search
     * @param  string $value    The value to search for
     * @param  string $position The wildcard position
     * @return Michaeljennings\Laralastica\Search
     */
    public function wildcard($field, $value, $position = 'both');

    /**
     * Adds a term search query to the query collection.
     *
     * @param string $field The index field.
     * @param mixed $value The value to match the term against
     * @return Michaeljennings\Laralastica\Search
     */
    public function term($field, $value);

    /**
     * Adds a terms search query to the query collection.
     *
     * @param  strgin $field  The index field
     * @param  array  $values An array of terms
     * @return Michaeljennings\Laralastica\Search
     */
    public function multiTerm($field, array $values);

    /**
     * Adds a fuzzy search query to the query collection.
     *
     * @param string $field The index field.
     * @param mixed $value The value to match against.
     * @return Michaeljennings\Laralastica\Search
     */
    public function fuzzy($field, $value);

    /**
     * Adds a match search query to the query collection.
     *
     * @param string $field The index field.
     * @param mixed $value The value to match against.
     * @param string $type The field type.
     * @return Michaeljennings\Laralastica\Search
     */
    public function match($field, $value, $type);

    /**
     * Adds a multi match search query to the query collection.
     *
     * @param array $fields The index fields.
     * @param mixed $value The value to match against.
     * @return Michaeljennings\Laralastica\Search
     */
    public function multiMatch($fields, $value);

    /**
     * Searches a field for a range of values.
     *
     * @param  strgin $field The field to search
     * @param  array  $args  The values to search between
     * @return Michaeljennings\Laralastica\Search
     */
    public function range($field, array $args);

    /**
     * Execute the search query and return the search results.
     *
     * @return array The search results.
     */
    public function results();

    /**
     * Delete a document by its id from the specified type
     *
     * @param  int   $id The id of the document
     * @return array     The response
     */
    public function delete($id);
}