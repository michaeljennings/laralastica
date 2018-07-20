<?php

namespace Michaeljennings\Laralastica\Tests;

use Michaeljennings\Laralastica\Events\IndexesWhenSaved;
use Michaeljennings\Laralastica\Events\RemovesDocumentWhenDeleted;
use Michaeljennings\Laralastica\Observer;
use Michaeljennings\Laralastica\Tests\Fixtures\TestModel;
use Michaeljennings\Laralastica\Tests\Fixtures\TestSoftDeleteModel;
use Mockery;

class ObserverTest extends TestCase
{
    /** @test */
    public function it_fires_the_index_when_saved_event_when_a_model_is_created()
    {
        $model = new TestModel();
        $dispatcher = Mockery::mock('Illuminate\Contracts\Events\Dispatcher')
                             ->shouldReceive('fire')
                             ->once()
                             ->with(Mockery::on(function ($event) {
                                 return $event instanceof IndexesWhenSaved;
                             }))
                             ->getMock();

        $observer = new Observer($dispatcher);

        $observer->created($model);
    }

    /** @test */
    public function it_fires_the_index_when_saved_event_when_a_model_is_saved()
    {
        $model = new TestModel();
        $dispatcher = Mockery::mock('Illuminate\Contracts\Events\Dispatcher')
                             ->shouldReceive('fire')
                             ->once()
                             ->with(Mockery::on(function ($event) {
                                 return $event instanceof IndexesWhenSaved;
                             }))
                             ->getMock();

        $observer = new Observer($dispatcher);

        $observer->saved($model);
    }

    /** @test */
    public function it_fires_the_index_when_saved_event_when_a_model_is_updated()
    {
        $dispatcher = Mockery::mock('Illuminate\Contracts\Events\Dispatcher')
                             ->shouldReceive('fire')
                             ->once()
                             ->with(Mockery::on(function ($event) {
                                 return $event instanceof IndexesWhenSaved;
                             }))
                             ->getMock();

        $observer = new Observer($dispatcher);
        $model = new TestModel();

        $observer->updated($model);
    }

    /** @test */
    public function it_fires_the_index_when_saved_event_when_a_model_is_restored()
    {
        $dispatcher = Mockery::mock('Illuminate\Contracts\Events\Dispatcher')
                             ->shouldReceive('fire')
                             ->once()
                             ->with(Mockery::on(function ($event) {
                                 return $event instanceof IndexesWhenSaved;
                             }))
                             ->getMock();

        $observer = new Observer($dispatcher);
        $model = new TestModel();

        $observer->restored($model);
    }

    /** @test */
    public function it_fires_the_remove_document_when_deleted_event_when_a_model_is_deleted()
    {
        $dispatcher = Mockery::mock('Illuminate\Contracts\Events\Dispatcher')
                             ->shouldReceive('fire')
                             ->once()
                             ->with(Mockery::on(function ($event) {
                                 return $event instanceof RemovesDocumentWhenDeleted;
                             }))
                             ->getMock();

        $observer = new Observer($dispatcher);
        $model = new TestModel();

        $observer->deleted($model);
    }

    /** @test */
    public function it_does_not_remove_the_document_if_the_model_search_soft_deletes()
    {
        $dispatcher = Mockery::mock('Illuminate\Contracts\Events\Dispatcher')
                             ->shouldReceive('fire')
                             ->times(0)
                             ->with(Mockery::on(function ($event) {
                                 return $event instanceof RemovesDocumentWhenDeleted;
                             }))
                             ->getMock();

        $observer = new Observer($dispatcher);
        $model = new TestSoftDeleteModel();

        $observer->deleted($model);
    }

    public function tearDown()
    {
        Mockery::close();

        parent::tearDown();
    }
}