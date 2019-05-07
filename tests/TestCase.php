<?php

namespace Michaeljennings\Laralastica\Tests;

use Elastica\Client;
use Elastica\Connection;
use Michaeljennings\Laralastica\LaralasticaServiceProvider;
use Michaeljennings\Laralastica\Tests\Fixtures\TestModel;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * Return a mock config array.
     *
     * @return array
     */
    protected function getConfig()
    {
        return [
            'driver' => 'elastica',
            'index' => 'testindex',
            'drivers' => [
                'elastica' => [
                    'host' => $this->getHost(),
                    'port' => $this->getPort(),
                    'size' => 10,
                ]
            ],
            'indexable' => [
                'test' => TestModel::class,
            ]
        ];
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return new Client($this->getConfig()['drivers']['elastica']);
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

    /**
     * @inheritdoc
     */
    protected function getPackageProviders($app)
    {
        return [
            LaralasticaServiceProvider::class,
        ];
    }
}
