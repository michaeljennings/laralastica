<?php

namespace Michaeljennings\Laralastica\Tests;

use Illuminate\Support\Facades\Event;
use Michaeljennings\Laralastica\Events\IndexesWhenSaved;
use Michaeljennings\Laralastica\Events\RemovesDocumentWhenDeleted;
use Michaeljennings\Laralastica\Observer;
use Michaeljennings\Laralastica\Tests\Fixtures\TestModel;
use Michaeljennings\Laralastica\Tests\Fixtures\TestSoftDeleteModel;

class ObserverTest extends TestCase
{
    /** @test */
    public function it_fires_the_index_when_saved_event_when_a_model_is_created()
    {
        $model = new TestModel();

        Event::fake([
            IndexesWhenSaved::class
        ]);

        $observer = new Observer();

        $observer->created($model);
    }

    /** @test */
    public function it_fires_the_index_when_saved_event_when_a_model_is_saved()
    {
        $model = new TestModel();

        Event::fake([
            IndexesWhenSaved::class
        ]);

        $observer = new Observer();

        $observer->saved($model);
    }

    /** @test */
    public function it_fires_the_index_when_saved_event_when_a_model_is_updated()
    {
        Event::fake([
            IndexesWhenSaved::class
        ]);

        $observer = new Observer();
        $model = new TestModel();

        $observer->updated($model);
    }

    /** @test */
    public function it_fires_the_index_when_saved_event_when_a_model_is_restored()
    {
        Event::fake([
            IndexesWhenSaved::class
        ]);

        $observer = new Observer();
        $model = new TestModel();

        $observer->restored($model);
    }

    /** @test */
    public function it_fires_the_remove_document_when_deleted_event_when_a_model_is_deleted()
    {
        Event::fake([
            RemovesDocumentWhenDeleted::class
        ]);

        $observer = new Observer();
        $model = new TestModel();

        $observer->deleted($model);
    }

    /** @test */
    public function it_does_not_remove_the_document_if_the_model_search_soft_deletes()
    {
        Event::fake([
            RemovesDocumentWhenDeleted::class
        ]);

        $observer = new Observer();
        $model = new TestSoftDeleteModel();

        $observer->deleted($model);
    }
}
