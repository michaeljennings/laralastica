<?php

namespace Michaeljennings\Laralastica\Listeners;

use Michaeljennings\Laralastica\Contracts\Laralastica;
use Michaeljennings\Laralastica\Events\RemovesDocumentWhenDeleted;

class RemovesDocumentBelongingToDeletedModel
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
     * Gets the saved model from the event and then transforms it to an array.
     * Then it indexes the saved model.
     *
     * @param RemovesDocumentWhenDeleted $event
     */
    public function handle(RemovesDocumentWhenDeleted $event)
    {
        $model = $event->getModel();

        $this->laralastica->delete($model->getIndex(), $model->getSearchKey());
    }
}