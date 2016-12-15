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

    class HelperTestCase extends TestCase
    {

    }

}