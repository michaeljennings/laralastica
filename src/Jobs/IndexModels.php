<?php

namespace Michaeljennings\Laralastica\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Michaeljennings\Laralastica\Events\IndexesWhenSaved;

class IndexModels implements ShouldQueue
{
    use Queueable, SerializesModels, InteractsWithQueue;

    /**
     * The models to be indexed.
     *
     * @var Collection
     */
    protected $models;

    public function __construct(Collection $models)
    {
        $this->models = $models;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        foreach ($this->models as $model) {
            event(new IndexesWhenSaved($model));
        }
    }
}