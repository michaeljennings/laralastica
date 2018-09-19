<?php

namespace Michaeljennings\Laralastica\Tests;

use Carbon\Carbon;
use Michaeljennings\Laralastica\LaralasticaServiceProvider;
use Michaeljennings\Laralastica\Tests\Fixtures\TestSoftDeleteModel;

class SearchSoftDeletesTest extends TestCase
{
    /**
     * @test
     */
    public function it_gets_the_indexable_attributes()
    {
        $model = factory(TestSoftDeleteModel::class)->create();

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
    public function it_gets_the_search_types_for_the_model()
    {
        $model = new TestSoftDeleteModel();
        $types = $model->getSearchDataTypes();

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
        $model = new TestSoftDeleteModel();

        $this->assertEquals('test_data', $model->getSearchType());
    }

    /**
     * @test
     */
    public function it_gets_the_search_key_for_the_model()
    {
        $model = factory(TestSoftDeleteModel::class)->create();

        $this->assertEquals($model->id, $model->getSearchKey());
    }

    /**
     * @test
     */
    public function it_gets_the_search_key_name()
    {
        $model = new TestSoftDeleteModel();

        $this->assertEquals('id', $model->getSearchKeyName());
    }

    /**
     * @test
     */
    public function it_gets_the_relative_key_name()
    {
        $model = new TestSoftDeleteModel();

        $this->assertEquals('test_data.id', $model->getRelativeSearchKey());
    }

    /** @test */
    public function it_transforms_the_attributes()
    {
        $model = factory(TestSoftDeleteModel::class)->create();
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
        factory(TestSoftDeleteModel::class)->create(['name' => 'Test']);
        factory(TestSoftDeleteModel::class)->create(['name' => 'Tests']);
        $shouldNotMatch = factory(TestSoftDeleteModel::class)->create(['name' => 'Test', 'deleted_at' => (string) new Carbon()]);

        $results = TestSoftDeleteModel::search(function($builder) {
            $builder->match('name', 'Test', function($query) {
                $query->setFieldFuzziness('name', 2);
            });
        })->get();

        $this->assertEquals(2, $results->count());
        $this->assertEquals('Test', $results->first()->name);
        $this->assertEquals('Tests', $results->last()->name);
        $this->assertNotContains($shouldNotMatch->id, $results->pluck('id')->all());
    }

    /**
     * @test
     */
    public function it_searches_only_trashed()
    {
        factory(TestSoftDeleteModel::class)->create(['name' => 'Tests', 'deleted_at' => (string) new Carbon()]);
        factory(TestSoftDeleteModel::class)->create(['name' => 'Test', 'deleted_at' => (string) new Carbon()]);
        $shouldNotMatch = factory(TestSoftDeleteModel::class)->create(['name' => 'Test']);

        $results = TestSoftDeleteModel::searchOnlyTrashed(function($builder) {
            $builder->match('name', 'Test', function($query) {
                $query->setFieldFuzziness('name', 2);
            });
        })->get();

        $this->assertEquals(2, $results->count());
        $this->assertEquals('Test', $results->first()->name);
        $this->assertEquals('Tests', $results->last()->name);
        $this->assertNotContains($shouldNotMatch->id, $results->pluck('id')->all());
    }

    /**
     * @test
     */
    public function it_searches_with_trashed()
    {
        factory(TestSoftDeleteModel::class)->create(['name' => 'Tests', 'deleted_at' => (string) new Carbon()]);
        factory(TestSoftDeleteModel::class)->create(['name' => 'Test', 'deleted_at' => (string) new Carbon()]);
        $notTrashed = factory(TestSoftDeleteModel::class)->create(['name' => 'Test']);

        $results = TestSoftDeleteModel::searchWithTrashed(function($builder) {
            $builder->match('name', 'Test', function($query) {
                $query->setFieldFuzziness('name', 2);
            });
        })->get();

        $this->assertEquals(3, $results->count());
        $this->assertEquals('Test', $results->first()->name);
        $this->assertEquals('Test', $results[1]->name);
        $this->assertEquals('Tests', $results->last()->name);
        $this->assertContains($notTrashed->id, $results->pluck('id')->all());
    }

    /**
     * @test
     */
    public function it_doesnt_bind_the_observer_if_the_dispatcher_is_not_set()
    {
        TestSoftDeleteModel::unsetEventDispatcher();

        $model = new TestSoftDeleteModel();

        $this->assertNull($model::saving(function() {}));
    }
}