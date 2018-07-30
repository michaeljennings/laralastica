<?php

namespace Michaeljennings\Laralastica\Listeners;

use Michaeljennings\Laralastica\Contracts\Laralastica;
use Michaeljennings\Laralastica\Events\IndexesWhenSaved;

class IndexesSavedModel
{
    /**
     * The laralastica instance.
     *
     * @var Laralastica
     */
    protected $laralastica;

    public function __construct(Laralastica $laralastica)
    {
        $this->laralastica = $laralastica;
    }

    /**
     * Create a new / update an elasticsearch document when the model
     * is saved.
     *
     * @param IndexesWhenSaved $event
     */
    public function handle(IndexesWhenSaved $event)
    {
        $model = $event->getModel();

        $attributes = $model->getIndexableAttributes($model);
        $attributes = $model->transformAttributes($attributes);

        $this->laralastica->add($model->getIndex(), $model->getSearchKey(), $attributes);
    }
}