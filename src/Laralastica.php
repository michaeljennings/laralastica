<?php namespace Michaeljennings\Laralastica; 

use Closure;
use Elastica\Client;
use Elastica\Index;

class Laralastica {

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
        return new Builder($this->client, $this->index, $type);
    }

}