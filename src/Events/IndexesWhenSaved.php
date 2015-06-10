<?php namespace Michaeljennings\Laralastica\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class IndexesWhenSaved {

    use SerializesModels;

    /**
     * The model that's been saved.
     *
     * @var Model
     */
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Return the saved model.
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

}