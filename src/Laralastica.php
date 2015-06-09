<?php namespace Michaeljennings\Laralastica; 

use Closure;
use Elastica\Client;
use Elastica\Index;
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
        $builder = $this->newBuilder($type);
        $builder->add($id, $data);

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
        $builder = $this->newBuilder($type);
        $builder->addMultiple($data);

        $this->refreshIndex();

        return $this;
    }

    /**
     * Run the provided queries on the type and then return the results.
     *
     * @param string $type
     * @param callable $query
     * @return mixed
     */
    public function search($type, Closure $query)
    {
        $builder = $this->newBuilder($type);
        $query($builder);

        if ( ! $builder->hasResults()) {
            $builder->results();
        }

        return $builder->getResults();
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
     * Create a new laralastica query builder.
     *
     * @param string $type The elasticsearch type to search
     * @return Builder
     */
    protected function newBuilder($type)
    {
        return new Builder($this->client, $this->index, $this->index->getType($type));
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