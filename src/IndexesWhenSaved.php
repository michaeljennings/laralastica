<?php namespace Michaeljennings\Laralastica;

trait IndexesWhenSaved {

    /**
     * Add the model event listener to index the model when it is saved.
     */
    protected static function bootIndexesWhenSaved()
    {
        static::saved(function($model)
        {
            $laralastica = app('Michaeljennings\Laralastica\Contracts\Wrapper');

            $laralastica->add($model->getType(), $model->getKey(), $model->toArray());
        });
    }

    /**
     * Get the elasticsearch type.
     *
     * @return mixed
     */
    public function getType()
    {
        return isset($this->type) ? $this->type : $this->table;
    }

}