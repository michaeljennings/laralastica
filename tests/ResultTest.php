<?php

namespace Michaeljennings\Laralastica\Tests;

use Michaeljennings\Laralastica\Result;

class ResultTest extends TestCase
{
    /** @test */
    public function it_implements_the_result_interface()
    {
        $result = $this->makeResult();

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Result::class, $result);
    }

    /** @test */
    public function it_gets_a_attribute_from_the_result()
    {
        $result = $this->makeResult();

        $this->assertEquals('bar', $result->get('foo'));
    }

    /** @test */
    public function it_gets_an_item_dynamically_from_the_result()
    {
        $result = $this->makeResult();

        $this->assertEquals('bar', $result->foo);
    }

    /**
     * @test
     */
    public function it_returns_null_if_the_attributes_is_not_set()
    {
        $result = $this->makeResult();

        $this->assertNull($result->test);
    }

    /** @test */
    public function it_converts_nested_items_to_a_result_instance()
    {
        $result = $this->makeResult();

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Result::class, $result->baz);
    }

    /**
     * @test
     */
    public function it_gets_the_result()
    {
        $result = new Result([], 'TestResult');

        $this->assertEquals('TestResult', $result->getResult());
    }

    /**
     * @test
     */
    public function it_gets_the_index()
    {
        $result = new Result([], null, 'testindex');

        $this->assertEquals('testindex', $result->getIndex());
    }

    /**
     * @test
     */
    public function it_gets_the_type()
    {
        $result = new Result([], null, 'testindex', 'testtype');

        $this->assertEquals('testtype', $result->getType());
    }

    /**
     * @test
     */
    public function it_gets_the_score()
    {
        $result = new Result([], null, 'testindex', 'testtype', 1.23);

        $this->assertEquals(1.23, $result->getScore());
    }

    protected function makeResult()
    {
        return new Result([
            'foo' => 'bar',
            'baz' => [
                'foo' => 'bar',
                'baz' => 'qux'
            ]
        ]);
    }
}