<?php

namespace Michaeljennings\Laralastica\Tests;

use Michaeljennings\Laralastica\IndexPrefixer;

class IndexPrefixerTest extends TestCase
{
    /**
     * @test
     */
    public function it_prefixes_the_index()
    {
        $prefixer = $this->makePrefixer();
        $index = 'test';

        config()->set('laralastica.index_prefix', 'testing_');

        $this->assertEquals('testing_test', $prefixer->prefix($index));
    }

    /**
     * @test
     */
    public function it_does_not_add_the_prefix_if_it_has_already_been_added()
    {
        $prefixer = $this->makePrefixer();
        $index = 'testing_test';

        config()->set('laralastica.index_prefix', 'testing_');

        $this->assertEquals('testing_test', $prefixer->prefix($index));
    }

    protected function makePrefixer()
    {
        return app(IndexPrefixer::class);
    }
}