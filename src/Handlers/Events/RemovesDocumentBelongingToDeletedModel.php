<?php namespace Michaeljennings\Laralastica\Handlers\Events;

use Elastica\Exception\NotFoundException;
use Michaeljennings\Laralastica\Contracts\Wrapper;
use Michaeljennings\Laralastica\Events\RemovesDocumentWhenDeleted;

class RemovesDocumentBelongingToDeletedModel {

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
     * @param RemovesDocumentWhenDeleted $event
     */
    public function handle(RemovesDocumentWhenDeleted $event)
    {
        $model = $event->getModel();

        try {
            $this->laralastica->delete($model->getSearchType(), $model->getSearchKey());
        } catch (NotFoundException $e) {
            //
        }
    }

}