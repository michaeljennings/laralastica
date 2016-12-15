<?php

namespace Michaeljennings\Laralastica\Tests;

use Michaeljennings\Laralastica\Contracts\ResultCollection;
use Michaeljennings\Laralastica\Laralastica;

class HelperTest extends HelperTestCase
{
    /** @test */
    public function assert_helper_returns_laralastica_instance()
    {
        $this->assertInstanceOf(Laralastica::class, laralastica());
    }

    /** @test */
    public function it_searches_if_parameters_are_passed_to_laralastica_helper()
    {
        $this->assertInstanceOf(ResultCollection::class, laralastica('test', function($query) {
            $query->matchAll();
        }));
    }
}