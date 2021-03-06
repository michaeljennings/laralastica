<?php

namespace Michaeljennings\Laralastica;

use Illuminate\Support\Collection;
use Michaeljennings\Laralastica\Contracts\ResultCollection as ResultCollectionContract;

class ResultCollection extends Collection implements ResultCollectionContract
{
    use HasQueryStats;
}