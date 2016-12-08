<?php

namespace Michaeljennings\Laralastica;

use Michaeljennings\Laralastica\Contracts\Result as ResultContract;

class Result implements ResultContract
{
    public function __construct(array $attributes)
    {
        foreach ($attributes as $key => $attribute) {
            if (is_array($attribute)) {
                $attributes[$key] = new Result($attribute);
            }
        }

        $this->attributes = $attributes;
    }

    /**
     * Get an attribute.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->attributes[$key];
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }
}