<?php

namespace Michaeljennings\Laralastica\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Michaeljennings\Laralastica\Events\IndexesWhenSaved;
use Michaeljennings\Laralastica\Jobs\IndexModels;

class ReIndexLaralastica extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laralastica:index {index?} {--queue}';

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
     */
    public function handle()
    {
        $models = $this->getIndexableModels($this->argument('index'));

        if ($this->option('queue')) {
            $this->indexViaQueue($models);
        } else {
            $this->index($models);
        }

        $this->info("\n\nThe re-indexing has been completed successfully\n");
    }

    /**
     * Re-index all of the provided models.
     *
     * @param $models
     */
    protected function index($models)
    {
        foreach ($models as $key => $model) {
            $this->info("\n\nRe-indexing " . $key . "\n");

            $toBeIndexed = $model->all();

            $bar = $this->output->createProgressBar(count($toBeIndexed));

            foreach ($toBeIndexed as $indexable) {
                $this->reIndex($indexable);

                $bar->advance();
            }

            $bar->finish();
        }
    }

    /**
     * Index the provided models via the queue.
     *
     * @param $models
     */
    protected function indexViaQueue($models)
    {
        foreach ($models as $key => $model) {
            $this->info("\n\nQueuing " . $key . "\n");

            $model->chunk(1000, function($indexable) {
                $this->dispatcher->dispatch(new IndexModels($indexable));
            });
        }
    }

    /**
     * Get the models to be re-indexed.
     *
     * @param string|null $index
     * @return array
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
