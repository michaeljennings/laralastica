<?php

namespace Michaeljennings\Laralastica\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Michaeljennings\Laralastica\SearchSoftDeletes;

class TestSoftDeleteModel extends Model
{
    use SearchSoftDeletes, SoftDeletes;

    protected $table = 'test_data';

    protected $guarded = [];

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
}