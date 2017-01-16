<?php

namespace Michaeljennings\Laralastica;

use Michaeljennings\Laralastica\Contracts\Laralastica;
use Michaeljennings\Laralastica\Events\IndexesWhenSaved;
use Michaeljennings\Laralastica\Exceptions\IndexableModelNotSetException;

class Indexer
{
    /**
     * The laralastica implementation.
     *
     * @var Laralastica
     */
    protected $laralastica;

    /**
     * The laralastica package config.
     *
     * @var array
     */
    protected $config;

    public function __construct(Laralastica $laralastica)
    {
        $this->laralastica = $laralastica;
        $this->config = config('laralastica') ?: [];
    }

    /**
     * Re-index the elasticsearch records.
     *
     * @param null $index
     */
    public function index($index = null)
    {
        $models = $this->getIndexableModels($index);

        foreach ($models as $key => $model) {
            $this->info('Re-indexing ' . $key);

            $toBeIndexed = $model->all();

            $bar = $this->output->createProgressBar(count($toBeIndexed));

            foreach ($toBeIndexed as $indexable) {
                $this->reIndex($indexable);

                $bar->advance();
            }

            $bar->finish();
        }

        $this->info('The re-indexing has been completed successfully');
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

        foreach ($this->config['indexable'] as $index =>  $indexable) {
            $collection[$index] = new $indexable;
        }

        return $collection;
    }

    /**
     * Re-index the provided model.
     *
     * @param Searchable $model
     */
    protected function reIndex(Searchable $model)
    {
        event(new IndexesWhenSaved($model));
    }
}