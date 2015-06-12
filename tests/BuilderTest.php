<?php namespace Michaeljennings\Laralastica\Tests; 

use Michaeljennings\Laralastica\Builder;

class BuilderTest extends Base {

    /**
     * @test
     */
    public function testMatchQuery()
    {
        $builder = $this->getBuilder();
        $query = $builder->match('foo', 'bar');

        $this->assertInstanceOf('Michaeljennings\Laralastica\Query', $query);

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\Match', $query[0]->getQuery());
    }

    /**
     * @test
     */
    public function testMultiMatchQuery()
    {
        $builder = $this->getBuilder();
        $query = $builder->multiMatch(['foo', 'bar'], 'bar');

        $this->assertInstanceOf('Michaeljennings\Laralastica\Query', $query);

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\MultiMatch', $query[0]->getQuery());
    }

    /**
     * @test
     */
    public function testFuzzyQuery()
    {
        $builder = $this->getBuilder();
        $query = $builder->fuzzy('foo', 'bar');

        $this->assertInstanceOf('Michaeljennings\Laralastica\Query', $query);

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\Fuzzy', $query[0]->getQuery());
    }

    /**
     * @test
     */
    public function testCommonQuery()
    {
        $builder = $this->getBuilder();
        $query = $builder->common('foo', 'bar');

        $this->assertInstanceOf('Michaeljennings\Laralastica\Query', $query);

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\Common', $query[0]->getQuery());
    }

    /**
     * @test
     */
    public function testMatchAllQuery()
    {
        $builder = $this->getBuilder();
        $query = $builder->matchAll();

        $this->assertInstanceOf('Michaeljennings\Laralastica\Query', $query);

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\MatchAll', $query[0]->getQuery());
    }

    /**
     * @test
     */
    public function testRangeQuery()
    {
        $builder = $this->getBuilder();
        $query = $builder->range('id', ['gt' => 1, 'lt' => 5]);

        $this->assertInstanceOf('Michaeljennings\Laralastica\Query', $query);

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\Range', $query[0]->getQuery());
    }

    /**
     * @test
     */
    public function testPrefixQuery()
    {
        $builder = $this->getBuilder();
        $query = $builder->prefix('foo', 'b');

        $this->assertInstanceOf('Michaeljennings\Laralastica\Query', $query);

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\Prefix', $query[0]->getQuery());
    }

    /**
     * @test
     */
    public function testRegexpQuery()
    {
        $builder = $this->getBuilder();
        $query = $builder->regexp('foo', 'b.*a');

        $this->assertInstanceOf('Michaeljennings\Laralastica\Query', $query);

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\Regexp', $query[0]->getQuery());
    }

    /**
     * @test
     */
    public function testTermQuery()
    {
        $builder = $this->getBuilder();
        $query = $builder->term('foo', 'baa');

        $this->assertInstanceOf('Michaeljennings\Laralastica\Query', $query);

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\Term', $query[0]->getQuery());
    }

    /**
     * @test
     */
    public function testTermsQuery()
    {
        $builder = $this->getBuilder();
        $query = $builder->terms('foo', ['baa']);

        $this->assertInstanceOf('Michaeljennings\Laralastica\Query', $query);

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\Terms', $query[0]->getQuery());
    }

    /**
     * @test
     */
    public function testWildcardQuery()
    {
        $builder = $this->getBuilder();
        $query = $builder->wildcard('foo', ['ba*']);

        $this->assertInstanceOf('Michaeljennings\Laralastica\Query', $query);

        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query\Wildcard', $query[0]->getQuery());
    }

    protected function getBuilder()
    {
        $client = $this->getClient();
        $config = $this->getConfig();
        $index = $client->getIndex($config['index']);

        return new Builder($client, $index, $index->getType('type'));
    }

}