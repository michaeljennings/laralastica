<?php

namespace Michaeljennings\Laralastica\Tests;

use Artisan;
use Michaeljennings\Laralastica\LaralasticaServiceProvider;
use Michaeljennings\Laralastica\Tests\Fixtures\TestModel;
use Orchestra\Database\ConsoleServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class ReIndexCommandTest extends OrchestraTestCase
{
    /**
     * @test
     */
    public function it_re_indexes_the_cluster()
    {
        Artisan::call('laralastica:index');

        $result = Artisan::output();

        $this->assertTrue(str_contains($result, 'Re-indexing test'));
        $this->assertTrue(str_contains($result, 'Re-indexing test2'));
        $this->assertTrue(str_contains($result, 'The re-indexing has been completed successfully'));
    }

    /**
     * @test
     */
    public function it_reindexes_a_specific_index()
    {
        Artisan::call('laralastica:index', [
            'index' => 'test'
        ]);

        $result = Artisan::output();

        $this->assertTrue(str_contains($result, 'Re-indexing test'));
        $this->assertTrue( ! str_contains($result, 'Re-indexing test2'));
        $this->assertTrue(str_contains($result, 'The re-indexing has been completed successfully'));
    }

    /**
     * @test
     */
    public function it_queues_the_re_indexing()
    {
        Artisan::call('laralastica:index', [
            '--queue' => true
        ]);

        $result = Artisan::output();

        $this->assertTrue(str_contains($result, 'Queuing test'));
        $this->assertTrue(str_contains($result, 'Queuing test2'));
        $this->assertTrue(str_contains($result, 'The re-indexing has been completed successfully'));
    }

    /**
     * @test
     */
    public function it_queues_a_specific_index_re_indexing()
    {
        Artisan::call('laralastica:index', [
            'index' => 'test',
            '--queue' => true
        ]);

        $result = Artisan::output();

        $this->assertTrue(str_contains($result, 'Queuing test'));
        $this->assertTrue( ! str_contains($result, 'Queuing test2'));
        $this->assertTrue(str_contains($result, 'The re-indexing has been completed successfully'));
    }

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        config()->set('laralastica.indexable', [
            'test' => TestModel::class,
            'test2' => TestModel::class,
        ]);

        $this->loadMigrationsFrom(realpath(__DIR__.'/database/migrations'));
        $this->artisan('migrate');

        $this->withFactories(__DIR__.'/database/factories');
    }

    protected function getPackageProviders($app)
    {
        return [LaralasticaServiceProvider::class, ConsoleServiceProvider::class];
    }
}