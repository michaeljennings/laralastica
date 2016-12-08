<?php

namespace Michaeljennings\Laralastica\Facades;

use Illuminate\Support\Facades\Facade;

class Laralastica extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laralastica';
    }
}