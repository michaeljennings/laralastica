<?php

namespace Michaeljennings\Laralastica\Tests;

use Elastica\Query\Common;
use Elastica\Query\Fuzzy;
use Elastica\Query\Match;
use Elastica\Query\MatchAll;
use Elastica\Query\QueryString;
use Elastica\Query\Range;
use Elastica\Query\Regexp;
use Elastica\Query\Term;
use Elastica\Query\Terms;
use Elastica\Query\Wildcard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Michaeljennings\Laralastica\Contracts\Driver;
use Michaeljennings\Laralastica\Contracts\Result;
use Michaeljennings\Laralastica\Drivers\ElasticaDriver;
use Michaeljennings\Laralastica\Query;
use Michaeljennings\Laralastica\ResultCollection;

class ElasticaDriverTest extends TestCase
{
    /** @test */
    public function it_returns_a_common_query()
    {
        $driver = $this->makeDriver();
        $query = $driver->common('foo', 'bar', 1.0);

        $this->assertInstanceOf(Common::class, $query);
    }

    /** @test */
    public function it_returns_a_common_query_and_runs_a_callback_on_it()
    {
        $driver = $this->makeDriver();
        $query = $driver->common('foo', 'bar', 1.0, function($query) {
            $query->setMinimumShouldMatch(5);
        });

        $this->assertInstanceOf(Common::class, $query);
        $this->assertEquals(5, $query->toArray()['common']['foo']['minimum_should_match']);
    }

    /** @test */
    public function it_returns_a_fuzzy_query()
    {
        $driver = $this->makeDriver();
        $query = $driver->fuzzy('foo', 'bar');

        $this->assertInstanceOf(Fuzzy::class, $query);
    }

    /** @test */
    public function it_returns_a_fuzzy_query_and_runs_a_callback_on_it()
    {
        $driver = $this->makeDriver();
        $query = $driver->fuzzy('foo', 'bar', function($query) {
            $query->setFieldOption('fuzziness', 2);
        });

        $this->assertInstanceOf(Fuzzy::class, $query);
        $this->assertEquals(2, $query->toArray()['fuzzy']['foo']['fuzziness']);
    }

    /** @test */
    public function it_returns_a_match_query()
    {
        $driver = $this->makeDriver();
        $query = $driver->match('foo', 'bar');

        $this->assertInstanceOf(Match::class, $query);
    }

    /** @test */
    public function it_returns_a_match_query_and_runs_a_callback_on_it()
    {
        $driver = $this->makeDriver();
        $query = $driver->match(null, null, function($query) {
            $query->setFieldBoost('foo');
        });

        $this->assertInstanceOf(Match::class, $query);
        $this->assertEquals(1, $query->toArray()['match']['foo']['boost']);
    }

    /** @test */
    public function it_returns_a_match_all_query()
    {
        $driver = $this->makeDriver();
        $query = $driver->matchAll();

        $this->assertInstanceOf(MatchAll::class, $query);
    }

    /** @test */
    public function it_returns_a_query_string_query()
    {
        $driver = $this->makeDriver();
        $query = $driver->queryString('testing');

        $this->assertInstanceOf(QueryString::class, $query);
    }

    /** @test */
    public function it_returns_a_query_string_query_and_runs_a_callback_on_it()
    {
        $driver = $this->makeDriver();
        $query = $driver->queryString('testing', function($query) {
            $query->setDefaultField('foo');
        });

        $this->assertInstanceOf(QueryString::class, $query);
        $this->assertEquals('foo', $query->toArray()['query_string']['default_field']);
    }

    /** @test */
    public function it_returns_a_range_query()
    {
        $driver = $this->makeDriver();
        $query = $driver->range('foo', ['gte' => 1, 'lte' => 20]);

        $this->assertInstanceOf(Range::class, $query);
    }

    /** @test */
    public function it_returns_a_range_query_and_runs_a_callback_on_it()
    {
        $driver = $this->makeDriver();
        $query = $driver->range('foo', ['gte' => 1, 'lte' => 20], function($query) {
            $query->setParam('foo', ['gte' => 1, 'lte' => 20, 'boost' => 1]);
        });

        $this->assertInstanceOf(Range::class, $query);
        $this->assertEquals(1, $query->toArray()['range']['foo']['boost']);
    }

    /** @test */
    public function it_returns_a_regexp_query()
    {
        $driver = $this->makeDriver();
        $query = $driver->regexp('foo', 'testing');

        $this->assertInstanceOf(Regexp::class, $query);
    }

    /** @test */
    public function it_returns_a_term_query()
    {
        $driver = $this->makeDriver();
        $query = $driver->term(['foo' => 'bar']);

        $this->assertInstanceOf(Term::class, $query);
    }

    /** @test */
    public function it_returns_a_term_query_and_runs_a_callback_on_it()
    {
        $driver = $this->makeDriver();
        $query = $driver->term(['foo' => 'bar'], function($query) {
            $query->setTerm('baz', 'qux', 2.0);
        });

        $this->assertInstanceOf(Term::class, $query);
        $this->assertEquals('2.0', $query->toArray()['term']['baz']['boost']);
    }

    /** @test */
    public function it_returns_a_terms_query()
    {
        $driver = $this->makeDriver();
        $query = $driver->terms('foo', ['bar', 'baz']);

        $this->assertInstanceOf(Terms::class, $query);
    }

    /** @test */
    public function it_returns_a_terms_query_and_run_a_callback_on_it()
    {
        $driver = $this->makeDriver();
        $query = $driver->terms('foo', ['bar', 'baz'], function($query) {
            $query->setMinimumMatch(5);
        });

        $this->assertInstanceOf(Terms::class, $query);
        $this->assertEquals(5, $query->toArray()['terms']['minimum_match']);
    }

    /** @test */
    public function it_returns_a_wildcard_query()
    {
        $driver = $this->makeDriver();
        $query = $driver->wildcard('foo', 'bar');

        $this->assertInstanceOf(Wildcard::class, $query);
    }

    /** @test */
    public function it_adds_a_document_to_the_index()
    {
        $driver = $this->makeDriver();
        $result = $driver->add('foo', 1, ['foo' => 'bar', 'id' => 1]);

        $this->assertInstanceOf(Driver::class, $result);
    }

    /** @test */
    public function it_adds_multiple_documents_to_the_index()
    {
        $driver = $this->makeDriver();

        $results = $driver->addMultiple('foo', [
            1 => [
                'id' => 1,
                'foo' => 'bar',
            ],
            2 => [
                'id' => 2,
                'foo' => 'baz',
            ],
        ]);

        $this->assertInstanceOf(Driver::class, $results);
    }

    /** @test */
    public function it_returns_a_results_collection()
    {
        $driver = $this->makeDriver();

        $driver->addMultiple('foo', [
            1 => [
                'id' => 1,
                'foo' => 'bar',
            ],
            2 => [
                'id' => 2,
                'foo' => 'baz',
            ],
        ]);

        $results = $driver->get('foo', []);

        $this->assertInstanceOf(ResultCollection::class, $results);
        $this->assertInstanceOf(Result::class, $results->first());
    }

    /** @test */
    public function it_returns_a_length_aware_paginator()
    {
        $driver = $this->makeDriver();

        $driver->addMultiple('foo', [
            1 => [
                'id' => 1,
                'foo' => 'bar',
            ],
            2 => [
                'id' => 2,
                'foo' => 'baz',
            ],
        ]);

        $results = $driver->paginate('foo', [], 1, 1, 0);

        $this->assertInstanceOf(LengthAwarePaginator::class, $results);
        $this->assertInstanceOf(Result::class, $results->first());
        $this->assertEquals(1, $results->count());
    }

    /** @test */
    public function it_deletes_a_documents_from_the_index()
    {
        $driver = $this->makeDriver();
        $driver->add('foo', 1, ['foo' => 'qux', 'id' => 1]);

        $result = $driver->delete('foo', 1);

        $this->assertInstanceOf(Driver::class, $result);
    }

    /** @test */
    public function it_sets_the_type_of_query_to_must()
    {
        $driver = $this->makeDriver();

        $driver->addMultiple('foo', [
            1 => [
                'id' => 1,
                'foo' => 'bar',
            ],
            2 => [
                'id' => 2,
                'foo' => 'baz',
            ],
        ]);

        $query = $driver->match('foo', 'bar');
        $query = new Query($query);

        $this->assertInstanceOf(Query::class, $query->must());

        $results = $driver->get('foo', [$query]);

        $this->assertInstanceOf(ResultCollection::class, $results);
        $this->assertInstanceOf(Result::class, $results->first());
    }

    /** @test */
    public function it_sets_the_type_of_query_to_should()
    {
        $driver = $this->makeDriver();

        $driver->addMultiple('foo', [
            1 => [
                'id' => 1,
                'foo' => 'bar',
            ],
            2 => [
                'id' => 2,
                'foo' => 'baz',
            ],
        ]);

        $query = $driver->match('foo', 'bar');
        $query = new Query($query);

        $this->assertInstanceOf(Query::class, $query->should());

        $results = $driver->get('foo', [$query]);

        $this->assertInstanceOf(ResultCollection::class, $results);
        $this->assertInstanceOf(Result::class, $results->first());
    }

    /** @test */
    public function it_sets_the_type_of_query_to_must_not()
    {
        $driver = $this->makeDriver();

        $driver->addMultiple('foo', [
            1 => [
                'id' => 1,
                'foo' => 'bar',
            ],
            2 => [
                'id' => 2,
                'foo' => 'baz',
            ],
        ]);

        $query = $driver->match('foo', 'bar');
        $query = new Query($query);

        $this->assertInstanceOf(Query::class, $query->mustNot());

        $results = $driver->get('foo', [$query]);

        $this->assertInstanceOf(ResultCollection::class, $results);
        $this->assertInstanceOf(Result::class, $results->first());
    }

    protected function makeDriver()
    {
        $client = $this->getClient();
        $index = $client->getIndex('testindex');

        return new ElasticaDriver($client, $index, $this->getConfig()['drivers']['elastica']);
    }
}