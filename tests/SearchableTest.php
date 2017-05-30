<?php

namespace Michaeljennings\Laralastica\Tests;

use Michaeljennings\Laralastica\LaralasticaServiceProvider;
use Michaeljennings\Laralastica\Tests\Fixtures\TestModel;
use Orchestra\Database\ConsoleServiceProvider;
use Orchestra\Testbench\TestCase;

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
    public function it_gets_the_search_types_for_the_model()
    {
        $model = new TestModel();
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
        $this->assertEquals('Tests', $results->first()->name);
        $this->assertEquals('Test', $results->last()->name);
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

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        $this->loadMigrationsFrom(realpath(__DIR__.'/database/migrations'));
        $this->artisan('migrate');

        $this->withFactories(__DIR__.'/database/factories');
    }

    protected function getPackageProviders($app)
    {
        return [LaralasticaServiceProvider::class, ConsoleServiceProvider::class];
    }

//    /** @test */
//    public function it_gets_the_indexable_attributes()
//    {
//        $model = new TestModel();
//        $attributes = $model->getIndexableAttributes($model);
//
//        $this->assertContains('1', $attributes);
//        $this->assertContains('test', $attributes);
//    }
//
//    /** @test */
//    public function it_gets_the_search_data_types()
//    {
//        $model = new TestModel();
//        $types = $model->getSearchDataTypes();
//
//        $this->assertArrayHasKey('id', $types);
//        $this->assertContains('int', $types);
//    }
//
//    /** @test */
//    public function it_gets_the_search_type_name()
//    {
//        $model = new TestModel();
//
//        $this->assertEquals('test', $model->getSearchType());
//    }
//
//    /** @test */
//    public function it_gets_the_search_key()
//    {
//        $model = new TestModel();
//
//        $this->assertEquals('1', $model->getSearchKey());
//    }
//
//    /** @test */
//    public function it_gets_the_search_key_name()
//    {
//        $model = new TestModel();
//
//        $this->assertEquals('id', $model->getSearchKeyName());
//    }
//
//    /** @test */
//    public function it_gets_the_relative_search_key()
//    {
//        $model = new TestModel();
//
//        $this->assertEquals('test.id', $model->getRelativeSearchKey());
//    }
//
//    /** @test */
//    public function it_transforms_the_attributes()
//    {
//        $model = new TestModel();
//        $attributes = $model->getIndexableAttributes($model);
//
//        $this->assertInternalType('string', $attributes['id']);
//        $this->assertInternalType('string', $attributes['sort_order']);
//        $this->assertInternalType('string', $attributes['name']);
//        $this->assertInternalType('string', $attributes['price']);
//        $this->assertInternalType('integer', $attributes['active']);
//        $this->assertInternalType('integer', $attributes['online']);
//
//        $attributes = $model->transformAttributes($attributes);
//
//        $this->assertInternalType('integer', $attributes['id']);
//        $this->assertInternalType('integer', $attributes['sort_order']);
//        $this->assertInternalType('string', $attributes['name']);
//        $this->assertInternalType('float', $attributes['price']);
//        $this->assertInternalType('boolean', $attributes['active']);
//        $this->assertInternalType('boolean', $attributes['online']);
//    }
//
//    /** @test */
//    public function it_gets_laralastica_by_its_test()
//    {
//        $model = new TestModel();
//
//        $query = Mockery::mock("Illuminate\\Database\\Query\\Builder");
//
//        $query->shouldReceive('whereIn')
//              ->once();
//
//        $query->shouldNotReceive('orderBy');
//
//        $model->scopeSearch($query, function ($builder) {
//            $builder->matchAll();
//        });
//    }
//
//    /**
//     * @test
//     */
//    public function it_doesnt_bind_the_observer_if_the_dispatcher_is_not_set()
//    {
//        TestModel::unsetEventDispatcher();
//
//        $model = new TestModel();
//
//        $this->assertNull($model::saving(function() {}));
//    }
//
//    public function tearDown()
//    {
//        Mockery::close();
//
//        parent::tearDown();
//    }
}