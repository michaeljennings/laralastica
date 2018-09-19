<?php

namespace Michaeljennings\Laralastica\Tests;

use Elastica\Query\Common;
use Elastica\Query\Exists;
use Elastica\Query\Fuzzy;
use Elastica\Query\Match;
use Elastica\Query\MatchAll;
use Elastica\Query\MatchPhrase;
use Elastica\Query\MatchPhrasePrefix;
use Elastica\Query\MultiMatch;
use Elastica\Query\QueryString;
use Elastica\Query\Range;
use Elastica\Query\Regexp;
use Elastica\Query\Term;
use Elastica\Query\Terms;
use Elastica\Query\Wildcard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Michaeljennings\Laralastica\Contracts\Driver;
use Michaeljennings\Laralastica\Contracts\Result;
use Michaeljennings\Laralastica\Drivers\ElasticaDriver;
use Michaeljennings\Laralastica\IndexPrefixer;
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
    public function it_returns_a_exists_query()
    {
        $driver = $this->makeDriver();
        $query = $driver->exists('foo');

        $this->assertInstanceOf(Exists::class, $query);
    }

    /** @test */
    public function it_returns_a_exists_query_and_runs_a_callback_on_it()
    {
        $driver = $this->makeDriver();
        $query = $driver->exists('foo', function($query) {
            $query->setParam('boost', 2);
        });

        $this->assertInstanceOf(Exists::class, $query);
        $this->assertEquals(2, $query->toArray()['exists']['boost']);
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
    public function it_returns_a_match_phrase_query()
    {
        $driver = $this->makeDriver();
        $query = $driver->matchPhrase('foo', 'bar');

        $this->assertInstanceOf(MatchPhrase::class, $query);
    }

    /** @test */
    public function it_returns_a_match_phrase_query_and_runs_a_callback_on_it()
    {
        $driver = $this->makeDriver();
        $query = $driver->matchPhrase(null, null, function($query) {
            $query->setFieldBoost('foo');
        });

        $this->assertInstanceOf(MatchPhrase::class, $query);
        $this->assertEquals(1, $query->toArray()['match_phrase']['foo']['boost']);
    }

    /** @test */
    public function it_returns_a_match_phrase_prefix_query()
    {
        $driver = $this->makeDriver();
        $query = $driver->matchPhrasePrefix('foo', 'bar');

        $this->assertInstanceOf(MatchPhrasePrefix::class, $query);
    }

    /** @test */
    public function it_returns_a_match_phrase_prefix_query_and_runs_a_callback_on_it()
    {
        $driver = $this->makeDriver();
        $query = $driver->matchPhrasePrefix(null, null, function($query) {
            $query->setFieldBoost('foo');
        });

        $this->assertInstanceOf(MatchPhrasePrefix::class, $query);
        $this->assertEquals(1, $query->toArray()['match_phrase_prefix']['foo']['boost']);
    }

    /** @test */
    public function it_returns_a_match_all_query()
    {
        $driver = $this->makeDriver();
        $query = $driver->matchAll();

        $this->assertInstanceOf(MatchAll::class, $query);
    }


    /** @test */
    public function it_returns_a_multi_match_prefix_query()
    {
        $driver = $this->makeDriver();
        $query = $driver->multiMatch(['foo'], 'bar');

        $this->assertInstanceOf(MultiMatch::class, $query);
    }

    /** @test */
    public function it_returns_a_multi_match_prefix_query_and_runs_a_callback_on_it()
    {
        $driver = $this->makeDriver();
        $query = $driver->multiMatch(null, null, function($query) {
            $query->setMinimumShouldMatch('70%');
        });

        $this->assertInstanceOf(MultiMatch::class, $query);
        $this->assertEquals('70%', $query->toArray()['multi_match']['minimum_should_match']);
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

    /**
     * @test
     */
    public function it_sets_the_minimum_should_match()
    {
        $driver = $this->makeDriver();
        $result = $driver->minimumShouldMatch(1);

        $this->assertInstanceOf(Driver::class, $result);
    }

    /**
     * @test
     */
    public function it_sets_the_minimum_should_match_using_the_alias()
    {
        $driver = $this->makeDriver();
        $result = $driver->setMinimumShouldMatch(1);

        $this->assertInstanceOf(Driver::class, $result);
    }

    /**
     * @test
     */
    public function it_sets_the_boost()
    {
        $driver = $this->makeDriver();
        $result = $driver->boost(1);

        $this->assertInstanceOf(Driver::class, $result);
    }

    /**
     * @test
     */
    public function it_sets_the_boost_using_the_alias()
    {
        $driver = $this->makeDriver();
        $result = $driver->setBoost(1);

        $this->assertInstanceOf(Driver::class, $result);
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

    /**
     * @test
     */
    public function it_sorts_the_results()
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

        $driver->matchAll();
        $driver->sort('_id');

        $results = $driver->get('foo', []);

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertEquals(2, $results->count());
        $this->assertEquals(1, $results->first()->id);
        $this->assertEquals(2, $results->last()->id);
    }

    /**
     * @test
     */
    public function it_offsets_the_first_result()
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

        $driver->from(1);
        $driver->matchAll();
        $driver->sort('_id');

        $results = $driver->get('foo', []);

        $this->assertInstanceOf(Collection::class, $results);

        $this->assertInstanceOf(Result::class, $results->first());
        $this->assertEquals(1, $results->count());
        $this->assertEquals(2, $results->first()->id);
    }

    /**
     * @test
     */
    public function it_limits_the_size_of_the_results()
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
            3 => [
                'id' => 3,
                'foo' => 'qux',
            ],
            4 => [
                'id' => 4,
                'foo' => 'foo',
            ],
        ]);

        $driver->from(1);
        $driver->size(2);
        $driver->matchAll();
        $driver->sort('_id');

        $results = $driver->get('foo', []);

        $this->assertInstanceOf(Collection::class, $results);

        $this->assertInstanceOf(Result::class, $results->first());
        $this->assertEquals(2, $results->count());
        $this->assertEquals(2, $results->first()->id);
        $this->assertEquals(3, $results->last()->id);
    }

    /**
     * @test
     */
    public function it_sets_the_track_scores_parameter()
    {
        $driver = $this->makeDriver();

        $driver->trackScores();

        $this->assertEquals(true, $driver->getParams()['track_scores']);
    }

    /**
     * @test
     */
    public function it_sets_the_highlight_parameter()
    {
        $driver = $this->makeDriver();

        $driver->highlight([]);

        $this->assertEquals([], $driver->getParams()['highlight']);
    }

    /**
     * @test
     */
    public function it_sets_the_explain_parameter()
    {
        $driver = $this->makeDriver();

        $driver->explain();

        $this->assertEquals(true, $driver->getParams()['explain']);
    }

    /**
     * @test
     */
    public function it_sets_the_stored_fields_parameter()
    {
        $driver = $this->makeDriver();

        $driver->storedFields(['foo']);

        $this->assertEquals(['foo'], $driver->getParams()['stored_fields']);
    }

    /**
     * @test
     */
    public function it_sets_the_field_data_values_parameter()
    {
        $driver = $this->makeDriver();

        $driver->fieldDataFields(['foo']);

        $this->assertEquals(['foo'], $driver->getParams()['docvalue_fields']);
    }

    /**
     * @test
     */
    public function it_sets_the_script_fields_parameter()
    {
        $driver = $this->makeDriver();

        $driver->scriptFields(['foo']);

        $this->assertEquals(['foo'], $driver->getParams()['script_fields']);
    }

    /**
     * @test
     */
    public function it_sets_the_min_score_parameter()
    {
        $driver = $this->makeDriver();

        $driver->minScore(5);

        $this->assertEquals(5, $driver->getParams()['min_score']);
    }

    /**
     * @test
     */
    public function it_sets_the_source_parameter()
    {
        $driver = $this->makeDriver();

        $driver->source(['foo']);

        $this->assertEquals(['foo'], $driver->getParams()['_source']);
    }

    /**
     * @test
     */
    public function it_sets_a_parameter()
    {
        $driver = $this->makeDriver();

        $driver->setParam('foo', 'bar');

        $this->assertEquals(['foo' => 'bar'], $driver->getParams());
    }

    /**
     * @test
     */
    public function it_sets_all_of_the_parameters()
    {
        $driver = $this->makeDriver();

        $driver->setParams(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $driver->getParams());
    }

    /**
     * @test
     */
    public function it_checks_if_a_parameter_is_set()
    {
        $driver = $this->makeDriver();

        $this->assertFalse($driver->hasParam('foo'));

        $driver->setParam('foo', 'bar');

        $this->assertTrue($driver->hasParam('foo'));
    }


    /**
     * @test
     */
    public function it_gets_all_of_the_parameters()
    {
        $driver = $this->makeDriver();

        $driver->setParams(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $driver->getParams());
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

    public function tearDown()
    {
        parent::tearDown();

        $client = $this->getClient();
        $index = $client->getIndex('testing_foo');

        if ($index->exists()) {
            $index->delete();
        }
    }

    protected function makeDriver()
    {
        $client = $this->getClient();

        return new ElasticaDriver($client, new IndexPrefixer(), config('laralastica.drivers.elastica'));
    }
}