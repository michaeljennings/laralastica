<?php namespace Michaeljennings\Laralastica; 

use Closure;
use Elastica\Client;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\Bool;
use Elastica\Search;
use Michaeljennings\Laralastica\Contracts\Wrapper;

class Laralastica implements Wrapper {

    /**
     * The package config.
     *
     * @var array
     */
    protected $config = [];

    /**
     * An instance of the elastica client.
     *
     * @var Client
     */
    protected $client;

    /**
     * The elastic search index being used.
     *
     * @var Index
     */
    protected $index;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = $this->newClient();
        $this->index = $this->newIndex();
    }

    /**
     * Add a new document to the provided type.
     *
     * @param string $type
     * @param string|int $id
     * @param array $data
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
     * @param array $data
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
     * Run the provided queries on the types and then return the results.
     *
     * @param string|array $types
     * @param callable $query
     * @param null|int $limit
     * @param null|int $offset
     * @return mixed
     */
    public function search($types, Closure $query, $limit = null, $offset = null)
    {
        $builder = $this->newQueryBuilder();
        $query($builder);

        $search = $this->newSearch($this->client, $this->index, $types);
        $query = $this->newQuery($builder->getQuery());

        if (is_int($limit)) {
            $query->setSize($limit);
        }

        if (is_int($offset)) {
            $query->setFrom($offset);
        }

        $search->setQuery($query);

        $results = $search->search();

        return $results->getresults();
    }

    /**
     * Delete a document from the provided type.
     *
     * @param string $type
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
     * Create a new elastica client.
     *
     * @return Client
     */
    protected function newClient()
    {
        return new Client([
            'host' => $this->config['host'],
            'port' => $this->config['port']
        ]);
    }

    /**
     * Get the elasticsearch index being used.
     *
     * @return Index
     */
    protected function newIndex()
    {
        if ( ! isset($this->client)) {
            $this->client = $this->newClient();
        }

        return $this->client->getIndex($this->config['index']);
    }

    /**
     * Get an elasticsearch type from its index.
     *
     * @param string $type
     * @return \Elastica\Type
     */
    protected function getType($type)
    {
        if ( ! isset($this->index)) {
            $this->index = $this->newIndex();
        }

        return $this->index->getType($type);
    }

    /**
     * Create a new laralastica query builder.
     *
     * @return Builder
     */
    protected function newQueryBuilder()
    {
        return new Builder();
    }

    /**
     * Create a new elastica search.
     *
     * @param Client $client
     * @param Index $index
     * @param string|array $types
     * @return Search
     */
    protected function newSearch(Client $client, Index $index, $types)
    {
        if (is_string($types)) {
            $types = [$types];
        }

        $search = new Search($client);

        $search->addIndex($index);
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
            $container = new Bool();

            foreach ($queries as $query) {
                $container->addMust($query);
            }

            $query = new Query($container);
            $query->addSort('_score');
        } else {
            $query = new Query();
        }

        return $query;
    }

    /**
     * Refreshes the elasticsearch index, should be run after adding
     * or deleting documents.
     *
     * @return \Elastica\Response
     */
    protected function refreshIndex()
    {
        return $this->index->refresh();
    }

}