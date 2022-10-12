<?php

namespace Michaeljennings\Laralastica\Tests;

use Elastica\Query\Match;
use Elastica\Query\MatchQuery;
use Michaeljennings\Laralastica\Query;

class QueryTest extends TestCase
{
    /** @test */
    public function it_implements_the_query_contract()
    {
        $query = $this->makeQuery();

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Query::class, $query);
    }

    /** @test */
    public function it_changes_the_type_to_must()
    {
        $query = $this->makeQuery();

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Query::class, $query->must());
        $this->assertEquals('must', $query->getType());
    }

    /** @test */
    public function it_changes_the_type_to_should()
    {
        $query = $this->makeQuery();

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Query::class, $query->should());
        $this->assertEquals('should', $query->getType());
    }

    /** @test */
    public function it_changes_the_type_to_must_not()
    {
        $query = $this->makeQuery();

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Query::class, $query->mustNot());
        $this->assertEquals('must_not', $query->getType());
    }

    /** @test */
    public function it_gets_the_raw_query()
    {
        $query = $this->makeQuery();

        $this->assertInstanceOf(MatchQuery::class, $query->getQuery());
    }

    protected function makeQuery()
    {
        return new Query(new MatchQuery());
    }
}
