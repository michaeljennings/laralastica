<?php namespace Michaeljennings\Laralastica\Tests; 

use Michaeljennings\Laralastica\Builder;

class BuilderTest extends Base {

    /**
     * @test
     */
    public function testMatchQuery()
    {
        $builder = $this->getBuilder();
        $builder->match('foo', 'bar');

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\Match', $query[0]);
    }

    /**
     * @test
     */
    public function testMultiMatchQuery()
    {
        $builder = $this->getBuilder();
        $builder->multiMatch(['foo', 'bar'], 'bar');

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\MultiMatch', $query[0]);
    }

    /**
     * @test
     */
    public function testFuzzyQuery()
    {
        $builder = $this->getBuilder();
        $builder->fuzzy('foo', 'bar');

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\Fuzzy', $query[0]);
    }

    /**
     * @test
     */
    public function testCommonQuery()
    {
        $builder = $this->getBuilder();
        $builder->common('foo', 'bar');

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\Common', $query[0]);
    }

    /**
     * @test
     */
    public function testMatchAllQuery()
    {
        $builder = $this->getBuilder();
        $builder->matchAll();

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\MatchAll', $query[0]);
    }

    /**
     * @test
     */
    public function testRangeQuery()
    {
        $builder = $this->getBuilder();
        $builder->range('id', ['gt' => 1, 'lt' => 5]);

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\Range', $query[0]);
    }

    /**
     * @test
     */
    public function testPrefixQuery()
    {
        $builder = $this->getBuilder();
        $builder->prefix('foo', 'b');

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\Prefix', $query[0]);
    }

    /**
     * @test
     */
    public function testRegexpQuery()
    {
        $builder = $this->getBuilder();
        $builder->regexp('foo', 'b.*a');

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\Regexp', $query[0]);
    }

    /**
     * @test
     */
    public function testTermQuery()
    {
        $builder = $this->getBuilder();
        $builder->term('foo', 'baa');

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\Term', $query[0]);
    }

    /**
     * @test
     */
    public function testTermsQuery()
    {
        $builder = $this->getBuilder();
        $builder->terms('foo', ['baa']);

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\Terms', $query[0]);
    }

    /**
     * @test
     */
    public function testWildcardQuery()
    {
        $builder = $this->getBuilder();
        $builder->wildcard('foo', ['ba*']);

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\Wildcard', $query[0]);
    }

    /**
     * @test
     */
    public function testAddMethodAddsDocument()
    {
        $builder = $this->getBuilder();
        $result = $builder->add(1, ['foo' => 'bar', 'bar' => 'baz']);

        $this->assertInstanceOf('Michaeljennings\Laralastica\Builder', $result);
    }

    /**
     * @test
     */
    public function testResultsReturnsAnArrayOfResults()
    {
        $builder = $this->getBuilder();

        $result = $builder->results();

        $this->assertInstanceOf('Michaeljennings\Laralastica\Builder', $result);

        $results = $result->getResults();

        $this->assertInternalType('array', $results);
    }

    /**
     * @test
     */
    public function testDeleteMethodDeletesDocuments()
    {
        $builder = $this->getBuilder();

        $result = $builder->delete(1);

        $this->assertEquals(200, $result->getStatus());
    }

    protected function getBuilder()
    {
        $client = $this->getClient();
        $config = $this->getConfig();
        $index = $client->getIndex($config['index']);

        return new Builder($client, $index, $index->getType('type'));
    }

}