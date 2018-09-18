<?php

namespace Michaeljennings\Laralastica\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class QueueIndexingModels implements ShouldQueue
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
     *
     * @param Dispatcher $dispatcher
     */
    public function handle(Dispatcher $dispatcher)
    {
        $dispatcher->dispatch(new IndexModels($this->models, $this->index));
    }
}