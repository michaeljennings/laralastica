<?php

namespace Michaeljennings\Laralastica\Tests;

use Michaeljennings\Laralastica\LengthAwarePaginator;

class LengthAwarePaginatorTest extends TestCase
{
    /** @test */
    public function it_implements_the_result_collection_contract()
    {
        $collection = $this->makePaginator();

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\ResultCollection::class, $collection);
    }

    /** @test */
    public function it_gets_the_total_hits()
    {
        $collection = $this->makePaginator();

        $this->assertEquals(0, $collection->totalHits());
    }

    /** @test */
    public function it_gets_the_max_score()
    {
        $collection = $this->makePaginator();

        $this->assertEquals(0, $collection->maxScore());
    }

    /** @test */
    public function it_gets_the_time_taken()
    {
        $collection = $this->makePaginator();

        $this->assertEquals(0.5, $collection->totalTime());
    }

    protected function makePaginator()
    {
        $paginator = new LengthAwarePaginator([], 0, 1, 1);

        return $paginator->setQueryStats(0, 0, 0.5);
    }
}