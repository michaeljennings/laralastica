<?php

namespace Michaeljennings\Laralastica\Tests\Events;

use Illuminate\Database\Eloquent\Model;
use Michaeljennings\Laralastica\Events\IndexesWhenSaved;
use Michaeljennings\Laralastica\Tests\Fixtures\TestModel;
use Michaeljennings\Laralastica\Tests\TestCase;

class IndexesWhenSavedTest extends TestCase
{
    /** @test */
    public function it_gets_the_model()
    {
        $model = new TestModel();
        $event = new IndexesWhenSaved($model);

        $this->assertInstanceOf(Model::class, $event->getModel());
    }
}