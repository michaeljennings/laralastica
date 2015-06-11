<?php namespace Michaeljennings\Laralastica\Tests; 

use Elastica\Client;
use Elastica\Connection;
use Elastica\Index;
use Illuminate\Http\Request;
use Michaeljennings\Laralastica\Laralastica;
use PHPUnit_Framework_TestCase;

class Base extends PHPUnit_Framework_TestCase {

    /**
     * @return Laralastica
     */
    protected function newLaralastica()
    {
        return new Laralastica($this->getConfig(), new Request());
    }

    /**
     * Return a mock config array.
     *
     * @return array
     */
    protected function getConfig()
    {
        return [
            'index' => 'testindex',
            'host' => $this->getHost(),
            'port' => $this->getPort(),
        ];
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        $config = [
            'host' => $this->getHost(),
            'port' => $this->getPort(),
        ];

        return new Client($config);
    }

    /**
     * @return string Host to es for elastica tests
     */
    protected function getHost()
    {
        return getenv('ES_HOST') ?: Connection::DEFAULT_HOST;
    }

    /**
     * @return int Port to es for elastica tests
     */
    protected function getPort()
    {
        return getenv('ES_PORT') ?: Connection::DEFAULT_PORT;
    }

}