<?php

namespace Michaeljennings\Laralastica;

use Illuminate\Pagination\LengthAwarePaginator as BaseLengthAwarePaginator;
use Michaeljennings\Laralastica\Contracts\ResultCollection as ResultCollectionContract;

class LengthAwarePaginator extends BaseLengthAwarePaginator implements ResultCollectionContract
{
    use HasQueryStats;
}