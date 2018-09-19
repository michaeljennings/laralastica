<?php

namespace Michaeljennings\Laralastica\Tests\Eloquent;

use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Michaeljennings\Laralastica\Eloquent\Builder;
use Michaeljennings\Laralastica\LengthAwarePaginator;
use Michaeljennings\Laralastica\ResultCollection;
use Michaeljennings\Laralastica\Tests\Fixtures\TestModel;
use Michaeljennings\Laralastica\Tests\TestCase;

class BuilderTest extends TestCase
{
    /**
     * @test
     */
    public function it_gets_the_total_hits()
    {
        $base = app(BaseBuilder::class);
        $builder = new Builder($base, 5, 1.3, 1.4);

        $this->assertEquals(5, $builder->totalHits());
    }

    /**
     * @test
     */
    public function it_gets_the_max_score()
    {
        $base = app(BaseBuilder::class);
        $builder = new Builder($base, 5, 1.3, 1.4);

        $this->assertEquals(1.3, $builder->maxScore());
    }

    /**
     * @test
     */
    public function it_gets_the_total_time()
    {
        $base = app(BaseBuilder::class);
        $builder = new Builder($base, 5, 1.3, 1.4);

        $this->assertEquals(1.4, $builder->totalTime());
    }

    /**
     * @test
     */
    public function it_returns_a_result_collection()
    {
        factory(TestModel::class)->create(['name' => 'Tests']);
        factory(TestModel::class)->create(['name' => 'Test']);

        $query = TestModel::query();
        $builder = new Builder($query, 2, 1, 1.5);

        $results = $builder->get();

        $this->assertInstanceOf(ResultCollection::class, $results);
        $this->assertEquals(2, $results->totalHits());
        $this->assertEquals(1.0, $results->maxScore());
        $this->assertEquals(1.5, $results->totalTime());
    }

    /**
     * @test
     */
    public function it_returns_the_correct_length_aware_paginator()
    {
        factory(TestModel::class)->create(['name' => 'Tests']);
        factory(TestModel::class)->create(['name' => 'Test']);

        $query = TestModel::query();
        $builder = new Builder($query, 2, 1, 1.5);

        $results = $builder->paginate(1);

        $this->assertInstanceOf(LengthAwarePaginator::class, $results);
        $this->assertEquals(2, $results->totalHits());
        $this->assertEquals(1.0, $results->maxScore());
        $this->assertEquals(1.5, $results->totalTime());
    }

    /**
     * @test
     */
    public function it_correctly_applies_the_queries_to_the_underlying_builder()
    {
        factory(TestModel::class)->create(['name' => 'Tests']);
        $match = factory(TestModel::class)->create(['name' => 'Test']);

        $query = TestModel::query();
        $builder = new Builder($query, 2, 1, 1.5);

        $builder->where('name', 'Test');

        $results = $builder->get();

        $this->assertInstanceOf(ResultCollection::class, $results);
        $this->assertEquals(1, $results->count());
        $this->assertEquals($match->id, $results->first()->id);
    }
}