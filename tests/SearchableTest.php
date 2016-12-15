<?php

namespace Michaeljennings\Laralastica\Tests;

use Michaeljennings\Laralastica\Tests\Fixtures\TestModel;

class SearchableTest extends TestCase
{
    /** @test */
    public function it_gets_the_indexable_attributes()
    {
        $model = new TestModel();
        $attributes = $model->getIndexableAttributes($model);

        $this->assertContains('1', $attributes);
        $this->assertContains('test', $attributes);
    }

    /** @test */
    public function it_gets_the_search_data_types()
    {
        $model = new TestModel();
        $types = $model->getSearchDataTypes();

        $this->assertArrayHasKey('id', $types);
        $this->assertContains('int', $types);
    }

    /** @test */
    public function it_gets_the_search_type_name()
    {
        $model = new TestModel();

        $this->assertEquals('test', $model->getSearchType());
    }

    /** @test */
    public function it_gets_the_search_key()
    {
        $model = new TestModel();

        $this->assertEquals('id', $model->getSearchKey());
    }

    /** @test */
    public function it_gets_the_relative_search_key()
    {
        $model = new TestModel();

        $this->assertEquals('test.id', $model->getRelativeSearchKey());
    }

    /** @test */
    public function it_transforms_the_attributes()
    {
        $model = new TestModel();
        $attributes = $model->getIndexableAttributes($model);

        $this->assertInternalType('string', $attributes['id']);
        $this->assertInternalType('string', $attributes['sort_order']);
        $this->assertInternalType('string', $attributes['name']);
        $this->assertInternalType('string', $attributes['price']);
        $this->assertInternalType('integer', $attributes['active']);
        $this->assertInternalType('integer', $attributes['online']);

        $attributes = $model->transformAttributes($attributecls);

        $this->assertInternalType('integer', $attributes['id']);
        $this->assertInternalType('integer', $attributes['sort_order']);
        $this->assertInternalType('string', $attributes['name']);
        $this->assertInternalType('float', $attributes['price']);
        $this->assertInternalType('boolean', $attributes['active']);
        $this->assertInternalType('boolean', $attributes['online']);
    }
}