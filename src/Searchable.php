<?php namespace Michaeljennings\Laralastica;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Michaeljennings\Laralastica\Events\IndexesWhenSaved;
use Michaeljennings\Laralastica\Events\RemovesDocumentWhenDeleted;

trait Searchable {

    protected $laralastica;

    /**
     * Add the model event listeners to fire the IndexesWhenSaved and
     * RemovesDocumentWhenDeleted events.
     */
    protected static function bootSearchable()
    {
        static::saved(function($model)
        {
            static::$dispatcher->fire(new IndexesWhenSaved($model));
        });

        static::deleted(function($model)
        {
            static::$dispatcher->fire(new RemovesDocumentWhenDeleted($model));
        });

        static::restored(function($model)
        {
            static::$dispatcher->fire(new IndexesWhenSaved($model));
        });
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
     * Return the key to be used when indexing a document.
     *
     * @return mixed
     */
    public function getSearchKey()
    {
        return $this->getKey();
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
                            $attribute = (int) $attribute;
                            break;
                        case "integer":
                            $attribute = (int) $attribute;
                            break;
                        case "string":
                            $attribute = (string) $attribute;
                            break;
                        case "float":
                            $attribute = (float) $attribute;
                            break;
                        case "bool":
                            $attribute = (bool) $attribute;
                            break;
                        case "boolean":
                            $attribute = (bool) $attribute;
                            break;
                    }

                    $attributes[$key] = $attribute;
                }
            }
        }

        return $attributes;
    }

    /**
     * Run the provided query on the elastic search index and then run a where in
     *
     * @param callable $query
     * @param callable $searchQuery
     * @param string $key
     * @param string $esKey
     * @return mixed
     */
    public function scopeSearch($query, Closure $searchQuery, $key = 'id', $esKey = 'id')
    {
        if ( ! isset($this->laralastica)) {
            $this->laralastica = app('laralastica');
        }

        $results = $this->laralastica->search($this->getSearchType(), $searchQuery);
        $values = [];

        foreach ($results as $result) {
            if (isset($result->$esKey)) {
                $values[] = $result->$esKey;
            }
        }

        return $query->whereIn($key, $values);
    }

}