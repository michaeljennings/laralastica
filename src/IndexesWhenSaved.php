<?php namespace Michaeljennings\Laralastica;

use Illuminate\Database\Eloquent\Model;
use Michaeljennings\Laralastica\Events\IndexesWhenSaved as IndexesWhenSavedEvent;

trait IndexesWhenSaved {

    /**
     * An array of columns with the desired data type as the value.
     *
     * @var array
     */
    protected $dataTypes = ['id' => 'int'];

    /**
     * Add the model event listener to index the model when it is saved.
     */
    protected static function bootIndexesWhenSaved()
    {
        static::saved(function($model)
        {
            static::$dispatcher->fire(new IndexesWhenSavedEvent($model));
        });
    }

    /**
     * Return the attributes to be indexed as an array.
     *
     * @param Model $model
     * @return array
     */
    public function getIndexableAttributes(Model $model)
    {
        return $model->getAttributes();
    }

    /**
     * Loop through the attributes and type cast them if neccesary.
     *
     * @param array $attributes
     * @return array
     */
    public function transformAttributes(array $attributes)
    {
        if ( ! empty($this->dataTypes)) {
            foreach ($attributes as &$attribute) {
                if (array_key_exists($attribute, $this->dataTypes)) {
                    switch ($this->dataTypes[$attribute]) {
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
                }
            }
        }

        return $attributes;
    }

}