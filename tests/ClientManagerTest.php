<?php

namespace Michaeljennings\Laralastica\Tests;

use Michaeljennings\Laralastica\ClientManager;
use Michaeljennings\Laralastica\Drivers\ElasticaDriver;
use Michaeljennings\Laralastica\Drivers\NullDriver;

class ClientManagerTest extends TestCase
{
    /**
     * @test
     */
    public function it_throws_an_exception_if_the_driver_is_not_specified()
    {
        $this->expectException(\Michaeljennings\Laralastica\Exceptions\DriverNotSetException::class);

        $manager = new ClientManager([]);

        $manager->getDefaultDriver();
    }

    /** @test */
    public function if_the_driver_is_null_it_returns_a_string_of_null()
    {
        $manager = new ClientManager(['driver' => null]);

        $this->assertEquals('null', $manager->getDefaultDriver());
    }

    /** @test */
    public function it_makes_the_elastica_driver()
    {
        $manager = new ClientManager(['driver' => 'elastica', 'index' => 'testindex']);

        $this->assertInstanceOf(ElasticaDriver::class, $manager->driver());
    }

    /** @test */
    public function it_makes_the_null_driver()
    {
        $manager = new ClientManager(['driver' => 'null']);

        $this->assertInstanceOf(NullDriver::class, $manager->driver());
    }

    /** @test */
    public function it_creates_a_new_driver_instance_if_the_driver_is_called_multiple_times()
    {
        $manager = new ClientManager(['driver' => 'null']);

        $driver1 = $manager->driver();
        $driver2 = $manager->driver();

        $this->assertNotEquals(spl_object_id($driver1), spl_object_id($driver2));
    }
}
