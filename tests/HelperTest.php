<?php

namespace Michaeljennings\Laralastica\Tests;

use Michaeljennings\Laralastica\Contracts\ResultCollection;
use Michaeljennings\Laralastica\Laralastica;
use Michaeljennings\Laralastica\LaralasticaServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class HelperTest extends OrchestraTestCase
{
    /** @test */
    public function assert_helper_returns_laralastica_instance()
    {
        $this->assertInstanceOf(Laralastica::class, laralastica());
    }

    /** @test */
    public function it_searches_if_parameters_are_passed_to_laralastica_helper()
    {
        laralastica()->add('test', 1, ['foo' => 'bar']);

        $this->assertInstanceOf(ResultCollection::class, laralastica('test', function($query) {
            $query->matchAll();
        }));
    }

    protected function getPackageProviders($app)
    {
        return [LaralasticaServiceProvider::class];
    }
}