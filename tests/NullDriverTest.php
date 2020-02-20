<?php

namespace Michaeljennings\Laralastica\Tests;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Michaeljennings\Laralastica\Contracts\Driver;
use Michaeljennings\Laralastica\Drivers\NullDriver;

class NullDriverTest extends TestCase
{
    /** @test */
    public function it_implements_the_driver_interface()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Driver::class, $driver);
    }

    /** @test */
    public function it_gets_an_empty_result_collection()
    {
        $driver = $this->makeDriver();
        $results = $driver->get('test', []);

        $this->assertEquals(0, $results->maxScore());
        $this->assertEquals(0, $results->totalHits());
        $this->assertEquals(0, $results->totalTime());
        $this->assertEquals(0, $results->count());
    }

    /** @test */
    public function it_gets_an_empty_length_aware_paginator()
    {
        $driver = $this->makeDriver();
        $results = $driver->paginate('test', [], 0, 15, 0);

        $this->assertInstanceOf(LengthAwarePaginator::class, $results);
        $this->assertEquals(0, $results->count());
    }

    /** @test */
    public function it_returns_null_when_creating_a_bool_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->bool([]));
    }

    /** @test */
    public function it_returns_null_when_creating_a_common_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->common('foo', 'bar', 1.0));
    }

    /** @test */
    public function it_returns_null_when_creating_an_exists_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->exists('foo'));
    }

    /** @test */
    public function it_returns_null_when_creating_a_fuzzy_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->fuzzy('foo', 'bar'));
    }

    /** @test */
    public function it_returns_null_when_creating_a_match_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->match());
    }

    /** @test */
    public function it_returns_null_when_creating_a_match_phrase_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->matchPhrase());
    }

    /** @test */
    public function it_returns_null_when_creating_a_match_phrase_prefix_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->matchPhrasePrefix());
    }

    /** @test */
    public function it_returns_null_when_creating_a_match_all_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->matchAll());
    }

    /** @test */
    public function it_returns_null_when_creating_a_multi_match_all_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->multiMatch());
    }

    /** @test */
    public function it_returns_null_when_creating_a_query_string_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->queryString('testing'));
    }

    /** @test */
    public function it_returns_null_when_creating_a_range_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->range());
    }

    /** @test */
    public function it_returns_null_when_creating_a_regexp_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->regexp());
    }

    /** @test */
    public function it_returns_null_when_creating_a_term_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->term());
    }

    /** @test */
    public function it_returns_null_when_creating_a_terms_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->terms());
    }

    /** @test */
    public function it_returns_null_when_creating_a_wildcard_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->wildcard());
    }

    /**
     * @test
     */
    public function it_returns_the_driver_when_setting_the_sort()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Driver::class, $driver->sort('_id'));
    }

    /**
     * @test
     */
    public function it_returns_the_driver_when_setting_the_from()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Driver::class, $driver->from(0));
    }

    /**
     * @test
     */
    public function it_returns_the_driver_when_setting_the_size()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Driver::class, $driver->from(10));
    }

    /**
     * @test
     */
    public function it_returns_the_driver_when_setting_the_minimum_should_match()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Driver::class, $driver->minimumShouldMatch(1));
    }

    /**
     * @test
     */
    public function it_returns_the_driver_when_setting_the_minimum_should_match_using_the_alias()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Driver::class, $driver->setMinimumShouldMatch(1));
    }

    /**
     * @test
     */
    public function it_returns_the_driver_when_setting_the_boost()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Driver::class, $driver->boost(1));
    }

    /**
     * @test
     */
    public function it_returns_the_driver_when_setting_the_boost_using_the_alias()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Driver::class, $driver->setBoost(1));
    }

    /**
     * @test
     */
    public function it_returns_the_driver_when_setting_the_track_scores_parameter()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Driver::class, $driver->trackScores());
    }

    /**
     * @test
     */
    public function it_returns_the_driver_when_setting_the_highlight_parameter()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Driver::class, $driver->highlight([]));
    }

    /**
     * @test
     */
    public function it_returns_the_driver_when_setting_the_explain_parameter()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Driver::class, $driver->explain());
    }

    /**
     * @test
     */
    public function it_returns_the_driver_when_setting_the_stored_fields_parameter()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Driver::class, $driver->storedFields([]));
    }

    /**
     * @test
     */
    public function it_returns_the_driver_when_setting_the_field_data_fields_parameter()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Driver::class, $driver->fieldDataFields([]));
    }

    /**
     * @test
     */
    public function it_returns_the_driver_when_setting_the_script_fields_parameter()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Driver::class, $driver->scriptFields([]));
    }

    /**
     * @test
     */
    public function it_returns_the_driver_when_setting_the_min_score_parameter()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Driver::class, $driver->minScore(5));
    }

    /**
     * @test
     */
    public function it_returns_the_driver_when_setting_the_source_parameter()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Driver::class, $driver->source([]));
    }

    /**
     * @test
     */
    public function it_returns_the_driver_when_setting_a_parameter()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Driver::class, $driver->setParam('foo', 'bar'));
    }

    /**
     * @test
     */
    public function it_returns_the_driver_when_setting_all_the_parameters()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Driver::class, $driver->setParams(['foo' => 'bar']));
    }

    /**
     * @test
     */
    public function it_returns_the_true_while_checking_if_a_parameter_exists()
    {
        $driver = $this->makeDriver();

        $this->assertTrue($driver->hasParam('foo'));
    }

    /**
     * @test
     */
    public function it_returns_an_empty_while_getting_the_parameters()
    {
        $driver = $this->makeDriver();

        $this->assertEquals([], $driver->getParams());
    }

    /** @test */
    public function it_returns_the_driver_instance_when_adding_a_document()
    {
        $driver = $this->makeDriver();
        $result = $driver->add('test', '1', []);

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Driver::class, $result);
    }

    /** @test */
    public function it_returns_the_driver_instance_when_adding_multiple_documents()
    {
        $driver = $this->makeDriver();
        $result = $driver->addMultiple('test', [
            '1' => [],
            '2' => [],
        ]);

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Driver::class, $result);
    }

    /** @test */
    public function it_returns_the_driver_instance_when_deleting_a_document()
    {
        $driver = $this->makeDriver();
        $result = $driver->delete('test', '1');

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Driver::class, $result);
    }

    protected function makeDriver()
    {
        return new NullDriver();
    }
}
