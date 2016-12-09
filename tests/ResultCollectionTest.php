<?php

namespace Michaeljennings\Laralastica\Tests;

use Michaeljennings\Laralastica\Result as ResultModel;
use Michaeljennings\Laralastica\ResultCollection;

class ResultCollectionTest extends TestCase
{
    /** @test */
    public function it_implements_the_result_collection_contract()
    {
        $collection = $this->makeResultCollection();

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\ResultCollection::class, $collection);
    }

    /** @test */
    public function it_gets_the_total_hits()
    {
        $collection = $this->makeResultCollection();

        $this->assertEquals(1, $collection->totalHits());
    }

    /** @test */
    public function it_gets_the_max_score()
    {
        $collection = $this->makeResultCollection();

        $this->assertEquals(1, $collection->maxScore());
    }

    /** @test */
    public function it_gets_the_time_taken()
    {
        $collection = $this->makeResultCollection();

        $this->assertEquals(0.5, $collection->totalTime());
    }

    protected function makeResultCollection()
    {
        $results = [
            new ResultModel([
                'foo' => 'bar',
                'baz' => [
                    'foo' => 'bar',
                    'baz' => 'qux'
                ]
            ])
        ];

        $collection = new ResultCollection($results);

        return $collection->setQueryStats(1, 1, 0.5);
    }
}