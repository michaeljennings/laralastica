<?php

namespace Michaeljennings\Laralastica\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Michaeljennings\Laralastica\Searchable;

class TestModel extends Model
{
    use Searchable;

    protected $attributes = [
        'id' => '1',
        'sort_order' => '10',
        'name' => 'test',
        'price' => '9.99',
        'active' => 1,
        'online' => 1,
    ];

    public function getTable()
    {
        return 'test';
    }

    public function getKeyName()
    {
        return 'id';
    }

    public function getSearchDataTypes()
    {
        return [
            'id' => 'int',
            'sort_order' => 'integer',
            'name' => 'string',
            'price' => 'float',
            'active' => 'bool',
            'online' => 'boolean',
        ];
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public static function resolveConnection($connection = null)
    {
        return null;
    }
}