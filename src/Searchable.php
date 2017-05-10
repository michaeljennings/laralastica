<?php namespace Michaeljennings\Laralastica;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Michaeljennings\Laralastica\Contracts\ResultCollection;
use Michaeljennings\Laralastica\Events\IndexesWhenSaved;
use Michaeljennings\Laralastica\Events\RemovesDocumentWhenDeleted;

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
     * Return an array of columns to be indexed with the column as the key and
     * the desired data type as the value.
     *
     * @return array
     */
    public function getSearchDataTypes()
    {
        return ['id' => 'int'];
    }

    /**
     * Get the elasticsearch type.
     *
     * @return string
     */
    public function getSearchType()
    {
        return $this->getTable();
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
     * Loop through the attributes and type cast them if neccesary.
     *
     * @param array $attributes
     * @return array
     */
    public function transformAttributes(array $attributes)
    {
        $searchDataTypes = $this->getSearchDataTypes();

        if ( ! empty($searchDataTypes)) {
            foreach ($attributes as $key => $attribute) {
                if (array_key_exists($key, $searchDataTypes)) {
                    switch ($searchDataTypes[$key]) {
                        case "int":
                            $attribute = (int)$attribute;
                            break;
                        case "integer":
                            $attribute = (int)$attribute;
                            break;
                        case "string":
                            $attribute = (string)$attribute;
                            break;
                        case "float":
                            $attribute = (float)$attribute;
                            break;
                        case "bool":
                            $attribute = (bool)$attribute;
                            break;
                        case "boolean":
                            $attribute = (bool)$attribute;
                            break;
                    }

                    $attributes[$key] = $attribute;
                }
            }
        }

        return $attributes;
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
        if ( ! isset($this->laralastica)) {
            $this->laralastica = laralastica();
        }

        $results = $this->laralastica->search($this->getSearchType(), $searchQuery);
        $searchKey = $key ?: $this->getSearchKeyName();
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
     * @param ResultCollection $values The values to order by
     * @param string $key The search key
     * @return string
     */
    protected function buildOrderByConstraints(ResultCollection $values, $key)
    {
        $relativeKey = $key ?: $this->getRelativeSearchKey();
        $order = "CASE $relativeKey ";
        foreach ($values as $key => $value) {
            $order .= 'WHEN ' . $value . ' THEN ' . $key . ' ';
        }

        $order .= 'END';

        return $order;
    }

}