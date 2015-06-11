<?php namespace Michaeljennings\Laralastica\Tests;

class LaralasticaTest extends Base {

    /**
     * @test
     */
    public function testThatAddMethodReturnsLaralasticaInstance()
    {
        $laralastica = $this->newLaralastica();

        $result = $laralastica->add('type', '1', ['foo' => 'bar', 'id' => 1]);

        $this->assertInstanceOf('Michaeljennings\Laralastica\Laralastica', $result);
    }

    /**
     * @test
     */
    public function testThatAddMultipleMethodReturnsLaralasticaInstance()
    {
        $laralastica = $this->newLaralastica();

        $documents = [
            '1' => [
                'id' => 1,
                'foo' => 'bar'
            ],
            '2' => [
                'id' => 2,
                'bar' => 'baz'
            ]
        ];

        $result = $laralastica->addMultiple('type', $documents);

        $this->assertInstanceOf('Michaeljennings\Laralastica\Laralastica', $result);
    }

    /**
     * @test
     */
    public function testThatDeleteMethodReturnsLaralasticaInstance()
    {
        $laralastica = $this->newLaralastica();

        $laralastica->add('type', '3', ['foo' => 'bar', 'id' => 1]);
        $result = $laralastica->delete('type', 3);

        $this->assertInstanceOf('Michaeljennings\Laralastica\Laralastica', $result);
    }

    /**
     * @expectedException \Elastica\Exception\NotFoundException
     * @test
     */
    public function testDeleteDocumentThatDoesNotExistsThrowsError()
    {
        $laralastica = $this->newLaralastica();
        $laralastica->delete('type', 'does not exist');
    }

    /**
     * @test
     */
    public function testAddMethodAddsDocument()
    {
        $laralastica = $this->newLaralastica();
        $result = $laralastica->add('type', 1, ['foo' => 'bar', 'bar' => 'baz']);

        $this->assertInstanceOf('Michaeljennings\Laralastica\Laralastica', $result);
    }

    /**
     * @test
     */
    public function testSearchReturnsAnArrayOfResults()
    {
        $laralastica = $this->newLaralastica();

        $results = $laralastica->search('type', function($q)
        {
            $q->matchAll();
        });

        $this->assertInternalType('array', $results);
    }

}