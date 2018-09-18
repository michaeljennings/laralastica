<?php

namespace Michaeljennings\Laralastica\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Michaeljennings\Laralastica\Events\IndexesWhenSaved;
use Michaeljennings\Laralastica\Exceptions\IndexableModelNotSetException;
use Michaeljennings\Laralastica\Jobs\IndexModels;
use Michaeljennings\Laralastica\Jobs\QueueIndexingModels;
use Michaeljennings\Laralastica\SearchSoftDeletes;

class ReIndexLaralastica extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laralastica:index {index?} {--queue} {--chunk=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-indexes all of the searchable models';

    /**
     * The laralastica package config.
     *
     * @var array
     */
    protected $config;

    /**
     * The job dispatcher.
     *
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * Create a new command instance.
     *
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        parent::__construct();

        $this->config = config('laralastica') ?: [];
        $this->dispatcher = $dispatcher;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws IndexableModelNotSetException
     */
    public function handle()
    {
        $models = $this->getIndexableModels($this->argument('index'));
        $chunks = $this->option('chunk') ?: 500;

        foreach ($models as $key => $model) {
            if ($this->option('queue')) {
                $this->indexViaQueue($key, $model, $chunks);
            } else {
                $this->index($key, $model, $chunks);
            }
        }

        $this->info("\n\nThe re-indexing has been completed successfully\n");
    }

    /**
     * Re-index all of the provided models.
     *
     * @param string $key
     * @param Model  $model
     * @param int    $chunks
     */
    protected function index($key, Model $model, $chunks)
    {
        $this->info("\n\nRe-indexing " . $key . "\n");

        $toBeIndexed = $this->toBeIndexed($model);

        $bar = $this->output->createProgressBar(count($toBeIndexed));

        foreach ($toBeIndexed->chunk($chunks) as $chunk) {
            $this->dispatcher->dispatch(new IndexModels($chunk, $model->getSearchType()));

            $bar->advance($chunk->count());
        }

        $bar->finish();
    }

    /**
     * Index the provided models via the queue.
     *
     * @param string $key
     * @param Model  $model
     * @param int    $chunks
     */
    protected function indexViaQueue($key, Model $model, $chunks)
    {
        $this->info("\n\nQueuing " . $key . "\n");

        $model->chunk($chunks, function ($indexable) use ($model) {
            $this->dispatcher->dispatch(new QueueIndexingModels($indexable, $model->getSearchType()));
        });
    }

    /**
     * Get the records to be indexed.
     *
     * @param Model $model
     * @return \Illuminate\Database\Eloquent\Collection|Model[]
     */
    protected function toBeIndexed(Model $model)
    {
        if (in_array(SearchSoftDeletes::class, class_uses($model))) {
            return $model->withTrashed()->get();
        }

        return $model->all();
    }

    /**
     * Get the models to be re-indexed.
     *
     * @param string|null $index
     * @return array
     * @throws IndexableModelNotSetException
     */
    protected function getIndexableModels($index = null)
    {
        if ($index) {
            return $this->getSpecificIndex($index);
        }

        return $this->getAllIndexableModels();
    }

    /**
     * Get an indexable model where an index has been specified.
     *
     * @param string $index
     * @return array
     * @throws IndexableModelNotSetException
     */
    protected function getSpecificIndex($index)
    {
        if ( ! isset($this->config['indexable'][$index])) {
            throw new IndexableModelNotSetException("There is no indexable model set with the key '$index'. Please make sure you've added it to the 'indexable' section of the laralastica config.");
        }

        $model = new $this->config['indexable'][$index];

        return [$index => $model];
    }

    /**
     * Get all of the models to be re-indexed.
     *
     * @return array
     */
    protected function getAllIndexableModels()
    {
        $collection = [];

        foreach ($this->config['indexable'] as $index => $indexable) {
            $collection[$index] = new $indexable;
        }

        return $collection;
    }

    /**
     * Re-index the provided model.
     *
     * @param Model $model
     */
    protected function reIndex(Model $model)
    {
        event(new IndexesWhenSaved($model));
    }
}
