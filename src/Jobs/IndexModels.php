<?php

namespace Michaeljennings\Laralastica\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class IndexModels
{
    use Queueable, SerializesModels, InteractsWithQueue;

    /**
     * The models to be indexed.
     *
     * @var Collection
     */
    protected $models;

    /**
     * The index to add the records to.
     *
     * @var string
     */
    protected $index;

    public function __construct(Collection $models, $index)
    {
        $this->models = $models;
        $this->index = $index;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $records = [];

        foreach ($this->models as $model) {
            $attributes = $model->getIndexableAttributes($model);
            $attributes = $model->transformAttributes($attributes);

            $records[$model->getSearchKey()] = $attributes;
        }

        laralastica()->addMultiple($this->index, $records);
    }
}