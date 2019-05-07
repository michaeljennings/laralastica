<?php

namespace Michaeljennings\Laralastica\Tests;

use Elastica\Client;
use Elastica\Connection;
use Michaeljennings\Laralastica\LaralasticaServiceProvider;
use Michaeljennings\Laralastica\Tests\Fixtures\TestModel;
use Orchestra\Database\ConsoleServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->withFactories(__DIR__.'/database/factories');
    }

    /**
     * @inheritdoc
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('laralastica.driver', 'elastica');
        $app['config']->set('laralastica.index_prefix', 'testing_');

        $app['config']->set('laralastica.indexable', [
            'test' => TestModel::class,
        ]);

        $app['config']->set('laralastica.drivers.elastica', [
            'host' => $this->getHost(),
            'port' => $this->getPort(),
            'size' => 10,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getPackageProviders($app)
    {
        return [
            ConsoleServiceProvider::class,
            LaralasticaServiceProvider::class,
        ];
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return app(Client::class);
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
