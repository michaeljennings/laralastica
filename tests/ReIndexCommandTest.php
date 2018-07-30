<?php

namespace Michaeljennings\Laralastica\Tests;

use Artisan;
use Michaeljennings\Laralastica\Tests\Fixtures\TestModel;

class ReIndexCommandTest extends TestCase
{
    /**
     * @test
     */
    public function it_re_indexes_the_cluster()
    {
        factory(TestModel::class, 3)->create();

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
        factory(TestModel::class, 3)->create();

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
        factory(TestModel::class, 3)->create();

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
        factory(TestModel::class, 3)->create();

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
     * @test
     * @group
     * @expectedException \Michaeljennings\Laralastica\Exceptions\IndexableModelNotSetException
     */
    public function it_throws_an_exception_if_the_specified_index_is_not_in_the_config()
    {
        Artisan::call('laralastica:index', [
            'index' => 'does_not_exist',
        ]);

        Artisan::output();
    }

    /**
     * Setup the test environment.
     */
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('laralastica.indexable', [
            'test' => TestModel::class,
            'test2' => TestModel::class,
        ]);
    }
}