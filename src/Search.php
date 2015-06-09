<?php namespace Michaeljennings\Laralastica;

use Elastica\Client;

class Search {

    /**
     * @var array
     */
    protected $config;

    /**
     * An instance of the elastica client.
     *
     * @var Client
     */
    protected $client;

    /**
     * The elasticsearch type.
     *
     * @var mixed
     */
    protected $type;

    /**
     * The elasticsearch index.
     *
     * @var mixed
     */
    protected $index;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        $this->client = $this->newClient();
    }

    /**
     * Set the elasticsearch index.
     *
     * @param string $index
     * @return $this
     */
    public function setIndex($index)
    {
        if ( ! isset($this->client)) {
            $this->newClient();
        }

        $this->index = $this->client->getIndex($index);

        return $this;
    }

    /**
     * Set the elasticsearch type.
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        if ( ! isset($this->index)) {
            $this->setIndex($this->config['index']);
        }

        $this->type = $this->index->getType($type);

        return $this;
    }

    /**
     * Create a new Elastica client.
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
     * Get a new instance of the client.
     */
    protected function refreshClient()
    {
        $this->client = $this->newClient();
    }

}