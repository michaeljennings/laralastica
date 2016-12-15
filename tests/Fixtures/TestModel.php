<?php

namespace Michaeljennings\Laralastica\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Michaeljennings\Laralastica\Searchable;

class TestModel extends Model
{
    use Searchable;

    public function getTable()
    {
        return 'test';
    }

    public function getKeyName()
    {
        return 'id';
    }

    public function getAttributes()
    {
        return [
            'id' => 1,
            'name' => 'test',
        ];
    }

    public static function resolveConnection($connection = null)
    {
        return null;
    }
}