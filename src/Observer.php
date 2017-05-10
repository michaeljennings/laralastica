<?php

namespace Michaeljennings\Laralastica;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Michaeljennings\Laralastica\Events\IndexesWhenSaved;
use Michaeljennings\Laralastica\Events\RemovesDocumentWhenDeleted;

class Observer
{
    /**
     * The event dispatcher instance.
     *
     * @var Dispatcher
     */
    protected $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Handle the created event for the model.
     *
     * @param Model $model
     */
    public function created(Model $model)
    {
        $this->dispatcher->fire(new IndexesWhenSaved($model));
    }
    
    /**
     * Handle the saved event for the model.
     *
     * @param Model $model
     */
    public function saved(Model $model)
    {
        $this->created($model);
    }

    /**
     * Handle the updated event for the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     */
    public function updated(Model $model)
    {
        $this->created($model);
    }

    /**
     * Handle the deleted event for the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function deleted(Model $model)
    {
        $this->dispatcher->fire(new RemovesDocumentWhenDeleted($model));
    }

    /**
     * Handle the restored event for the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     */
    public function restored(Model $model)
    {
        $this->created($model);
    }
}
