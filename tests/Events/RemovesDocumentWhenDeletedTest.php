<?php

namespace Michaeljennings\Laralastica\Tests\Events;

use Illuminate\Database\Eloquent\Model;
use Michaeljennings\Laralastica\Events\RemovesDocumentWhenDeleted;
use Michaeljennings\Laralastica\Tests\Fixtures\TestModel;
use Michaeljennings\Laralastica\Tests\TestCase;

class RemovesDocumentWhenDeletedTest extends TestCase
{
    /** @test */
    public function it_gets_the_model()
    {
        $model = new TestModel();
        $event = new RemovesDocumentWhenDeleted($model);

        $this->assertInstanceOf(Model::class, $event->getModel());
    }
}