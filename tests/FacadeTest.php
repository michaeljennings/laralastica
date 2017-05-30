<?php

namespace Michaeljennings\Laralastica\Tests;

use Michaeljennings\Laralastica\LaralasticaServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class FacadeTest extends OrchestraTestCase
{
    /**
     * @test
     */
    public function it_loads_laralastica_from_its_facade()
    {
        $result = \Laralastica::add('foo', 1, ['foo' => 'bar', 'id' => 1]);

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Laralastica::class, $result);
    }

    protected function getPackageProviders($app)
    {
        return [LaralasticaServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Laralastica' => 'Michaeljennings\Laralastica\Facades\Laralastica'
        ];
    }
}