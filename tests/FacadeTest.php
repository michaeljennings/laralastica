<?php

namespace Michaeljennings\Laralastica\Tests;

class FacadeTest extends TestCase
{
    /**
     * @test
     */
    public function it_loads_laralastica_from_its_facade()
    {
        $result = \Laralastica::add('foo', 1, ['foo' => 'bar', 'id' => 1]);

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Laralastica::class, $result);
    }

    protected function getPackageAliases($app)
    {
        return [
            'Laralastica' => 'Michaeljennings\Laralastica\Facades\Laralastica'
        ];
    }
}