<?php namespace Michaeljennings\Laralastica;

trait IndexesWhenSaved {

    protected static function bootIndexesWhenSaved()
    {
        static::created(function($model)
        {

        });
    }

}