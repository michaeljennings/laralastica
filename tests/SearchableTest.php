<?php

namespace Michaeljennings\Laralastica\Tests;

use Michaeljennings\Laralastica\LaralasticaServiceProvider;
use Michaeljennings\Laralastica\Tests\Fixtures\TestModel;

class SearchableTest extends TestCase
{
    /**
     * @test
     */
    public function it_gets_the_indexable_attributes()
    {
        $model = factory(TestModel::class)->create();

        $attributes = $model->getIndexableAttributes($model);

        $this->assertArrayHasKey('id', $attributes);
        $this->assertArrayHasKey('sort_order', $attributes);
        $this->assertArrayHasKey('name', $attributes);
        $this->assertArrayHasKey('price', $attributes);
        $this->assertArrayHasKey('active', $attributes);
        $this->assertArrayHasKey('online', $attributes);
    }

    /**
     * @test
     */
    public function it_gets_the_castable_attributes_for_the_model()
    {
        $model = new TestModel();
        $types = $model->getLaralasticaCasts();

        $this->assertArrayHasKey('id', $types);
        $this->assertArrayHasKey('sort_order', $types);
        $this->assertArrayHasKey('name', $types);
        $this->assertArrayHasKey('price', $types);
        $this->assertArrayHasKey('active', $types);
        $this->assertArrayHasKey('online', $types);
    }

    /**
     * @test
     */
    public function it_gets_the_elasticsearch_type_for_the_model()
    {
        $model = new TestModel();

        $this->assertEquals('test_data', $model->getSearchType());
    }

    /**
     * @test
     */
    public function it_gets_the_search_key_for_the_model()
    {
        $model = factory(TestModel::class)->create();

        $this->assertEquals($model->id, $model->getSearchKey());
    }

    /**
     * @test
     */
    public function it_gets_the_search_key_name()
    {
        $model = new TestModel();

        $this->assertEquals('id', $model->getSearchKeyName());
    }

    /**
     * @test
     */
    public function it_gets_the_relative_key_name()
    {
        $model = new TestModel();

        $this->assertEquals('test_data.id', $model->getRelativeSearchKey());
    }

    /** @test */
    public function it_transforms_the_attributes()
    {
        $model = factory(TestModel::class)->create();
        $attributes = $model->getIndexableAttributes($model);

        $this->assertInternalType('integer', $attributes['id']);
        $this->assertInternalType('integer', $attributes['sort_order']);
        $this->assertInternalType('string', $attributes['name']);
        $this->assertInternalType('float', $attributes['price']);
        $this->assertInternalType('boolean', $attributes['active']);
        $this->assertInternalType('boolean', $attributes['online']);

        $attributes = $model->transformAttributes($attributes);

        $this->assertInternalType('integer', $attributes['id']);
        $this->assertInternalType('integer', $attributes['sort_order']);
        $this->assertInternalType('string', $attributes['name']);
        $this->assertInternalType('float', $attributes['price']);
        $this->assertInternalType('boolean', $attributes['active']);
        $this->assertInternalType('boolean', $attributes['online']);
    }

    /**
     * @test
     */
    public function it_gets_the_search_results()
    {
        factory(TestModel::class)->create(['name' => 'Tests']);
        factory(TestModel::class)->create(['name' => 'Test']);

        $results = TestModel::search(function($builder) {
            $builder->match('name', 'Test', function($query) {
                $query->setFieldFuzziness('name', 2);
            });
        })->get();

        $this->assertEquals(2, $results->count());
        $this->assertEquals('Test', $results->first()->name);
        $this->assertEquals('Tests', $results->last()->name);
    }

    /**
     * @test
     */
    public function it_doesnt_bind_the_observer_if_the_dispatcher_is_not_set()
    {
        TestModel::unsetEventDispatcher();

        $model = new TestModel();

        $this->assertNull($model::saving(function() {}));
    }
}