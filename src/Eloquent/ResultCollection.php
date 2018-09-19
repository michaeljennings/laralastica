<?php

namespace Michaeljennings\Laralastica\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Michaeljennings\Laralastica\Contracts\ResultCollection as ResultCollectionContract;
use Michaeljennings\Laralastica\HasQueryStats;

class ResultCollection extends Collection implements ResultCollectionContract
{
    use HasQueryStats;
}