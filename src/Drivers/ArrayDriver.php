<?php

namespace Michaeljennings\Laralastica\Drivers;

use Elastica\Document;
use Elastica\Query\AbstractQuery;
use Elastica\Query\Common;
use Elastica\Query\Fuzzy;
use Elastica\Query\MatchAll;
use Michaeljennings\Laralastica\Contracts\Driver;

class ArrayDriver implements Driver
{

    public function get($types, array $queries)
    {
        // TODO: Implement get() method.
    }

    public function common($field, $query, $cutoffFrequency, callable $callback = null)
    {
        // TODO: Implement common() method.
    }

    public function fuzzy($field, $value, callable $callback = null)
    {
        // TODO: Implement fuzzy() method.
    }

    public function match($field = null, $value = null, callable $callback = null)
    {
        // TODO: Implement match() method.
    }

    public function matchAll()
    {
        // TODO: Implement matchAll() method.
    }

    public function queryString($query = '', callable $callback = null)
    {
        // TODO: Implement queryString() method.
    }

    public function range($fieldName = null, $args = [], callable $callback = null)
    {
        // TODO: Implement range() method.
    }

    public function regexp($key = '', $value = null, $boost = 1.0, callable $callback = null)
    {
        // TODO: Implement regexp() method.
    }

    public function term(array $terms = [], callable $callback = null)
    {
        // TODO: Implement term() method.
    }

    public function terms($key = '', array $terms = [], callable $callback = null)
    {
        // TODO: Implement terms() method.
    }

    public function wildcard($key = '', $value = null, $boost = 1.0, callable $callback = null)
    {
        // TODO: Implement wildcard() method.
    }

    public function add($type, $id, array $data)
    {
        // TODO: Implement add() method.
    }

    public function addMultiple($type, array $data)
    {
        // TODO: Implement addMultiple() method.
    }

    public function delete($type, $id)
    {
        // TODO: Implement delete() method.
    }
}