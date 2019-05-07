<?php

namespace Michaeljennings\Laralastica;

use Illuminate\Database\Eloquent\Model;
use Michaeljennings\Laralastica\Events\IndexesWhenSaved;
use Michaeljennings\Laralastica\Events\RemovesDocumentWhenDeleted;

class Observer
{
    /**
     * Handle the created event for the model.
     *
     * @param Model $model
     */
    public function created(Model $model)
    {
        event(new IndexesWhenSaved($model));
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
        if ( ! in_array(SearchSoftDeletes::class, class_uses($model))) {
            event(new RemovesDocumentWhenDeleted($model));
        }
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
