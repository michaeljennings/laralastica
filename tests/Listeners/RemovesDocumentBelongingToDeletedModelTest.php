<?php

namespace Michaeljennings\Laralastica\Tests\Listeners;

use Michaeljennings\Laralastica\Events\RemovesDocumentWhenDeleted;
use Michaeljennings\Laralastica\Listeners\RemovesDocumentBelongingToDeletedModel;
use Michaeljennings\Laralastica\Tests\TestCase;
use Mockery;

class RemovesDocumentBelongingToDeletedModelTest extends TestCase
{
    /** @test */
    public function it_handles_deleting_a_document()
    {
        $laralastica = Mockery::mock('Michaeljennings\Laralastica\Laralastica')
                              ->shouldReceive('delete')
                              ->once()
                              ->getMock();

        $model = Mockery::mock('Illuminate\Database\Eloquent\Model');

        $model->shouldReceive('getIndex')
              ->once();

        $model->shouldReceive('getSearchKey')
              ->once();

        $event = new RemovesDocumentWhenDeleted($model);

        $handler = new RemovesDocumentBelongingToDeletedModel($laralastica);

        $handler->handle($event);
    }
}