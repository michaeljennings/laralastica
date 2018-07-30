<?php

namespace Michaeljennings\Laralastica\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Michaeljennings\Laralastica\Searchable;

class TestModel extends Model
{
    use Searchable;

    protected $table = 'test_data';

    protected $guarded = [];

    protected $casts = [
        'id' => 'int',
        'sort_order' => 'integer',
    ];

    protected $laralasticaCasts = [
        'name' => 'string',
        'price' => 'float',
        'active' => 'bool',
        'online' => 'boolean',
    ];
}