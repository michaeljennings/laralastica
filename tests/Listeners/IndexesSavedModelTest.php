<?php

namespace Michaeljennings\Laralastica\Tests\Listeners;

use Michaeljennings\Laralastica\Events\IndexesWhenSaved;
use Michaeljennings\Laralastica\Listeners\IndexesSavedModel;
use Michaeljennings\Laralastica\Tests\TestCase;
use Mockery;

class IndexesSavedModelTest extends TestCase
{
    /** @test */
    public function it_handles_adding_a_new_document()
    {
        $laralastica = Mockery::mock('Michaeljennings\Laralastica\Laralastica')
                              ->shouldReceive('add')
                              ->once()
                              ->getMock();

        $model = Mockery::mock('Illuminate\Database\Eloquent\Model');

        $model->shouldReceive('getIndexableAttributes')
              ->once()
              ->andReturn(['id' => 1, 'name' => 'test']);

        $model->shouldReceive('transformAttributes')
              ->once()
              ->andReturn(['id' => 1, 'name' => 'test']);

        $model->shouldReceive('getIndex')
              ->once();

        $model->shouldReceive('getSearchKey')
              ->once();

        $event = new IndexesWhenSaved($model);

        $handler = new IndexesSavedModel($laralastica);

        $handler->handle($event);
    }

    public function tearDown()
    {
        Mockery::close();

        parent::tearDown();
    }
}