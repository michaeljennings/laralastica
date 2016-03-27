<?php namespace Michaeljennings\Laralastica\Handlers\Events;

use Michaeljennings\Laralastica\Contracts\Wrapper;
use Michaeljennings\Laralastica\Events\IndexesWhenSaved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class IndexesSavedModel implements ShouldQueue {

 	use InteractsWithQueue;
    /**
     * An instance of the laralastica search wrapper.
     *
     * @var Wrapper
     */
    protected $laralastica;

    public function __construct(Wrapper $laralastica)
    {
        $this->laralastica = $laralastica;
    }

    /**
     * Gets the saved model from the event and then transforms it to an array.
     * Then it indexes the saved model.
     *
     * @param IndexesWhenSaved $event
     */
    public function handle(IndexesWhenSaved $event)
    {
        $model = $event->getModel();

        $attributes = $model->getIndexableAttributes($model);
        $attributes = $model->transformAttributes($attributes);

        $this->laralastica->add($model->getSearchType(), $model->getSearchKey(), $attributes);
    }

}