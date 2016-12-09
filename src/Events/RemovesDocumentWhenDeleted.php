<?php

namespace Michaeljennings\Laralastica\Events;

class RemovesDocumentWhenDeleted
{
    /**
     * The deleted model.
     *
     * @var Model
     */
    protected $model;

    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Return the deleted model.
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }
}