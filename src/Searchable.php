<?php

namespace Michaeljennings\Laralastica;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Michaeljennings\Laralastica\Contracts\Builder;
use Michaeljennings\Laralastica\Events\IndexesWhenSaved;
use Michaeljennings\Laralastica\Events\RemovesDocumentWhenDeleted;
use Michaeljennings\Laralastica\SearchSoftDeletes;

trait Searchable
{
    /**
     * The laralastica instance.
     *
     * @var Laralastica
     */
    protected $laralastica;

    /**
     * Boot the trait.
     */
    protected static function bootSearchable()
    {
        if ( ! is_null(static::$dispatcher)) {
            static::observe(new Observer(static::$dispatcher));
        }
    }

    /**
     * Return an array of attributes to be indexed.
     *
     * @param Model $model
     * @return array
     */
    public function getIndexableAttributes(Model $model)
    {
        return $model->getAttributes();
    }

    /**
     * Get the fields that should be cast.
     *
     * @return array
     */
    public function getLaralasticaCasts()
    {
        return array_merge($this->getCasts(), isset($this->laralasticaCasts) ? $this->laralasticaCasts : []);
    }

    /**
     * Get the elasticsearch index.
     *
     * @return string
     */
    public function getIndex()
    {
        return strtolower($this->getTable());
    }

    /**
     * Get the elasticsearch type.
     *
     * @deprecated Update to use getIndex.
     * @return string
     */
    public function getSearchType()
    {
        return $this->getIndex();
    }

    /**
     * Return the search key value.
     *
     * @return mixed
     */
    public function getSearchKey()
    {
        return $this->getAttribute($this->getSearchKeyName());
    }

    /**
     * Return the key to be used when indexing a document.
     *
     * @return mixed
     */
    public function getSearchKeyName()
    {
        return $this->getKeyName();
    }

    /**
     * Get the full path for the search key.
     *
     * @return string
     */
    public function getRelativeSearchKey()
    {
        return $this->getTable() . '.' . $this->getSearchKeyName();
    }

    /**
     * Loop through the attributes and type cast them if necessary.
     *
     * @param array $attributes
     * @return array
     */
    public function transformAttributes(array $attributes)
    {
        $casts = $this->getLaralasticaCasts();

        if ( ! empty($casts)) {
            foreach ($attributes as $key => $value) {
                $attributes[$key] = $this->transformAttribute($casts, $key, $value);
            }
        }

        return $attributes;
    }

    /**
     * Cast the attribute to the required type.
     *
     * @param array  $casts
     * @param string $key
     * @param mixed  $value
     * @return bool|float|int|string
     */
    protected function transformAttribute(array $casts, $key, $value)
    {
        if (array_key_exists($key, $casts)) {
            switch ($casts[$key]) {
                case "int":
                case "integer":
                    return (int)$value;
                    break;
                case "string":
                    return (string)$value;
                    break;
                case 'real':
                case 'float':
                case 'double':
                    return (float)$value;
                    break;
                case 'bool':
                case 'boolean':
                    return (bool)$value;
                    break;
                default:
                    return $value;
                    break;
            }
        }

        return $value;
    }

    /**
     * Run the provided query on the elastic search index and then run a where
     * in.
     *
     * @param callable    $query
     * @param callable    $searchQuery
     * @param string|null $key
     * @param bool        $sortByResults
     * @return mixed
     */
    public function scopeSearch($query, callable $searchQuery, $key = null, $sortByResults = true)
    {
        return $this->runSearch($query, $searchQuery, $key, $sortByResults);
    }

    /**
     * Execute the elasticsearch query and then scope the query.
     *
     * @param mixed       $query
     * @param callable    $searchQuery
     * @param string|null $key
     * @param bool        $sortByResults
     * @return mixed
     */
    protected function runSearch($query, callable $searchQuery, $key = null, $sortByResults = true)
    {
        if ( ! isset($this->laralastica)) {
            $this->laralastica = laralastica();
        }

        $results = $this->laralastica->search($this->getIndex(), $searchQuery);
        $searchKey = $key ?: $this->getRelativeSearchKey();
        $values = $results->map(function ($result) {
            return $result->{$this->getSearchKeyName()};
        });

        if ($values instanceof Collection) {
            $values = $values->all();
        }

        if ($sortByResults && ! $results->isEmpty()) {
            $query->orderBy(\DB::raw($this->buildOrderByConstraints($values, $key)));
        }

        return $query->whereIn($searchKey, $values);
    }

    /**
     * Build the order by constraint
     *
     * @param mixed  $values The values to order by
     * @param string $key    The search key
     * @return string
     */
    protected function buildOrderByConstraints($values, $key)
    {
        $relativeKey = $key ?: $this->getRelativeSearchKey();
        $order = "CASE $relativeKey ";
        foreach ($values as $key => $value) {
            if ($value) {
                $order .= 'WHEN ' . $value . ' THEN ' . $key . ' ';
            }
        }

        $order .= 'END';

        return $order;
    }

}