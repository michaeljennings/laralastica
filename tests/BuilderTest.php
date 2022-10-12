<?php

namespace Michaeljennings\Laralastica\Tests;

use Elastica\Query\Exists;
use Elastica\Query\MatchQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Michaeljennings\Laralastica\Builder;
use Michaeljennings\Laralastica\ClientManager;
use Michaeljennings\Laralastica\Contracts\Query;
use Michaeljennings\Laralastica\Contracts\Result;
use Michaeljennings\Laralastica\Exceptions\DriverMethodDoesNotExistException;
use Michaeljennings\Laralastica\ResultCollection;

class BuilderTest extends TestCase
{
    /** @test */
    public function it_implements_the_builder_interface()
    {
        $builder = $this->makeBuilder();

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Builder::class, $builder);
    }

    /** @test */
    public function it_dynamically_calls_methods_on_the_driver()
    {
        $builder = $this->makeBuilder();

        $query = $builder->matchQuery('foo', 'bar');

        $this->assertInstanceOf(Query::class, $query);
    }

    /** @test */
    public function it_adds_a_raw_query()
    {
        $builder = $this->makeBuilder();
        $query = new MatchQuery();

        $query = $builder->query($query);

        $this->assertInstanceOf(Query::class, $query);
    }

    /**
     * @test
     */
    public function it_adds_a_boolean_query()
    {
        $builder = $this->makeBuilder();

        $query = $builder->bool(function($builder) {
            $builder->matchAll();
        });

        $this->assertInstanceOf(Query::class, $query);
    }

    /** @test */
    public function it_adds_a_filter()
    {
        $builder = $this->makeBuilder();
        $query = new Exists('foo');

        $filter = $builder->filter($query);

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Filter::class, $filter);
    }

    /** @test */
    public function it_adds_a_filter_using_a_callback()
    {
        $builder = $this->makeBuilder();

        $filter = $builder->filter(function($builder) {
            $query = new Exists('foo');

            $builder->query($query);
        });

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Filter::class, $filter);
        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Builder::class, $filter->getFilter());
    }

    /** @test */
    public function it_adds_a_document_to_the_index()
    {
        $builder = $this->makeBuilder();
        $result = $builder->add('foo', 1, ['foo' => 'bar', 'id' => 1]);

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Builder::class, $result);
    }

    /** @test */
    public function it_adds_multiple_documents_to_the_index()
    {
        $builder = $this->makeBuilder();

        $results = $builder->addMultiple('foo', [
            1 => [
                'id' => 1,
                'foo' => 'bar',
            ],
            2 => [
                'id' => 2,
                'foo' => 'baz',
            ],
        ]);

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Builder::class, $results);
    }

    /** @test */
    public function it_deletes_a_documents_from_the_index()
    {
        $builder = $this->makeBuilder();
        $builder->add('foo', 1, ['foo' => 'qux', 'id' => 1]);

        $result = $builder->delete('foo', 1);

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Builder::class, $result);
    }

    /** @test */
    public function it_returns_a_results_collection()
    {
        $builder = $this->makeBuilder();

        $builder->addMultiple('foo', [
            1 => [
                'id' => 1,
                'foo' => 'bar',
            ],
            2 => [
                'id' => 2,
                'foo' => 'baz',
            ],
        ]);

        $results = $builder->get('foo', []);

        $this->assertInstanceOf(ResultCollection::class, $results);
        $this->assertInstanceOf(Result::class, $results->first());
    }

    /** @test */
    public function it_returns_a_length_aware_paginator()
    {
        $builder = $this->makeBuilder();

        $builder->addMultiple('foo', [
            1 => [
                'id' => 1,
                'foo' => 'bar',
            ],
            2 => [
                'id' => 2,
                'foo' => 'baz',
            ],
        ]);

        $results = $builder->paginate('foo', [], 1, 1, 0);

        $this->assertInstanceOf(LengthAwarePaginator::class, $results);
        $this->assertInstanceOf(Result::class, $results->first());
        $this->assertEquals(1, $results->count());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_the_driver_method_does_not_exist()
    {
        $this->expectException(DriverMethodDoesNotExistException::class);

        $builder = $this->makeBuilder();

        $builder->doesNotExist();
    }

    protected function makeBuilder()
    {
        return new Builder(new ClientManager(config('laralastica')));
    }
}
