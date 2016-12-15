<?php

namespace {

    use Elastica\Connection;
    use Illuminate\Http\Request;
    use Michaeljennings\Laralastica\ClientManager;
    use Michaeljennings\Laralastica\Laralastica;

    function app()
    {
        $request = new Request(['page' => 1]);

        return new Laralastica(new ClientManager([
            'driver' => 'null',
            'index' => 'testindex',
            'drivers' => [
                'elastica' => [
                    'host' => getenv('ES_HOST') ?: Connection::DEFAULT_HOST,
                    'port' => getenv('ES_PORT') ?: Connection::DEFAULT_PORT,
                    'size' => 10,
                ]
            ]
        ]), $request);
    }
}

namespace Michaeljennings\Laralastica\Tests {

    use Michaeljennings\Laralastica\Contracts\ResultCollection;
    use Michaeljennings\Laralastica\Laralastica;

    class HelperTest extends TestCase
    {
        /** @test */
        public function assert_helper_returns_laralastica_instance()
        {
            $this->assertInstanceOf(Laralastica::class, laralastica());
        }
        
        /** @test */
        public function it_searches_if_parameters_are_passed_to_laralastica_helper()
        {
            $this->assertInstanceOf(ResultCollection::class, laralastica('test', function($query) {
                $query->matchAll();
            }));
        }
    }
}