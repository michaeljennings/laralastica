<?php

namespace Michaeljennings\Laralastica\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Michaeljennings\Laralastica\Events\IndexesWhenSaved;

class ReIndexLaralastica extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laralastica:index {index?}';

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
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->config = config('laralastica') ?: [];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $models = $this->getIndexableModels($this->argument('index'));

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

        $this->info("\n\nThe re-indexing has been completed successfully\n");
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
