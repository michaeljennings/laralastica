<?php

namespace Michaeljennings\Laralastica\Tests;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Michaeljennings\Laralastica\ClientManager;
use Michaeljennings\Laralastica\Contracts\Result;
use Michaeljennings\Laralastica\Laralastica;
use Michaeljennings\Laralastica\ResultCollection;

class LaralasticaTest extends TestCase
{
    /** @test */
    public function it_implements_the_laralastica_contract()
    {
        $laralastica = $this->makeLaralastica();

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Laralastica::class, $laralastica);
    }

    /** @test */
    public function it_adds_a_document_to_the_index()
    {
        $laralastica = $this->makeLaralastica();
        $result = $laralastica->add('foo', 1, ['foo' => 'bar', 'id' => 1]);

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Laralastica::class, $result);
    }

    /** @test */
    public function it_adds_multiple_documents_to_the_index()
    {
        $laralastica = $this->makeLaralastica();

        $results = $laralastica->addMultiple('foo', [
            1 => [
                'id' => 1,
                'foo' => 'bar',
            ],
            2 => [
                'id' => 2,
                'foo' => 'baz',
            ],
        ]);

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Laralastica::class, $results);
    }

    /** @test */
    public function it_deletes_a_documents_from_the_index()
    {
        $laralastica = $this->makeLaralastica();
        $laralastica->add('foo', 1, ['foo' => 'qux', 'id' => 1]);

        $result = $laralastica->delete('foo', 1);

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Laralastica::class, $result);
    }

    /** @test */
    public function it_searches_the_index_and_returns_a_result_collection()
    {
        $laralastica = $this->makeLaralastica();

        $laralastica->addMultiple('foo', [
            1 => [
                'id' => 1,
                'foo' => 'bar',
            ],
            2 => [
                'id' => 2,
                'foo' => 'baz',
            ],
        ]);

        $results = $laralastica->search('foo', function($builder) {
            $builder->matchAll();
        });

        $this->assertInstanceOf(ResultCollection::class, $results);
        $this->assertInstanceOf(Result::class, $results->first());
    }

    /** @test */
    public function it_searches_the_index_and_returns_a_length_aware_paginator()
    {
        $laralastica = $this->makeLaralastica();

        $laralastica->addMultiple('foo', [
            1 => [
                'id' => 1,
                'foo' => 'bar',
            ],
            2 => [
                'id' => 2,
                'foo' => 'baz',
            ],
        ]);

        $results = $laralastica->paginate('foo', function($builder) {
            $builder->matchAll();
        }, 1);

        $this->assertInstanceOf(LengthAwarePaginator::class, $results);
        $this->assertInstanceOf(Result::class, $results->first());
        $this->assertEquals(1, $results->count());
    }

    protected function makeLaralastica()
    {
        $request = new Request(['page' => 1]);

        return new Laralastica(new ClientManager(config('laralastica')), $request);
    }
}