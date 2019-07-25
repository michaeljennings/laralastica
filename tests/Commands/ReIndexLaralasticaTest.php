<?php

namespace Michaeljennings\Laralastica\Tests\Commands;

use Michaeljennings\Laralastica\Tests\Fixtures\TestModel;
use Michaeljennings\Laralastica\Tests\TestCase;

class ReIndexLaralasticaTest extends TestCase
{
    /**
     * @test
     */
    public function it_reindexes_the_model()
    {
        // Set the indexable model
        config()->set('laralastica.indexable', [
            'test' => TestModel::class,
            'another_test' => TestModel::class,
        ]);

        // Create some models
        factory(TestModel::class, 3)->create();

        $this->artisan('laralastica:index')
             ->expectsOutput("\n\nRe-indexing test\n")
             ->expectsOutput("\n\nRe-indexing another_test\n")
             ->expectsOutput("\n\nThe re-indexing has been completed successfully\n");
    }

    /**
     * @test
     */
    public function it_reindexes_a_specific_index()
    {
        // Set the indexable model
        config()->set('laralastica.indexable', [
            'test' => TestModel::class,
            'another_test' => TestModel::class,
        ]);

        // Create some models
        factory(TestModel::class, 3)->create();

        $this->artisan('laralastica:index test')
             ->expectsOutput("\n\nRe-indexing test\n")
             ->expectsOutput("\n\nThe re-indexing has been completed successfully\n");
    }

    /**
     * @test
     */
    public function it_queues_the_reindexing()
    {
        // Set the indexable model
        config()->set('laralastica.indexable', [
            'test' => TestModel::class,
            'another_test' => TestModel::class,
        ]);

        // Create some models
        factory(TestModel::class, 3)->create();

        $this->artisan('laralastica:index --queue')
             ->expectsOutput("\n\nQueuing test\n")
             ->expectsOutput("\n\nQueuing another_test\n")
             ->expectsOutput("\n\nThe re-indexing has been completed successfully\n");
    }

    /**
     * @test
     */
    public function it_changes_the_chunking()
    {
        // Set the indexable model
        config()->set('laralastica.indexable', [
            'test' => TestModel::class,
            'another_test' => TestModel::class,
        ]);

        // Create some models
        factory(TestModel::class, 3)->create();

        $this->artisan('laralastica:index --chunk=2')
             ->expectsOutput("\n\nRe-indexing test\n")
             ->expectsOutput("\n\nRe-indexing another_test\n")
             ->expectsOutput("\n\nThe re-indexing has been completed successfully\n");
    }

    /**
     * @test
     */
    public function it_brings_relations_to_index()
    {
        // Set the indexable model
        config()->set('laralastica.indexable', [
            'test' => [
                'model' => TestModel::class,
                'with' => [
                    'parent'
                ]
            ]
        ]);

        // Create some models
        factory(TestModel::class, 3)->create();

        $this->artisan('laralastica:index --chunk=2')
             ->expectsOutput("\n\nRe-indexing test\n")
             ->expectsOutput("\n\nThe re-indexing has been completed successfully\n");
    }
}
