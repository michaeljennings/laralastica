<?php

namespace Michaeljennings\Laralastica;

use Michaeljennings\Laralastica\Contracts\Result as ResultContract;

class Result implements ResultContract
{
    /**
     * The raw result from the elasticsearch driver.
     *
     * @var mixed
     */
    protected $result;

    /**
     * The index that was queried.
     *
     * @var string
     */
    protected $index;

    /**
     * The type of result.
     *
     * @var string
     */
    protected $type;

    /**
     * Return the score of the result.
     *
     * @var float
     */
    protected $score;

    /**
     * The underlying attributes.
     *
     * @var array
     */
    protected $attributes;

    /**
     * Construct a new result.
     *
     * @param mixed|null  $result
     * @param string|null $index
     * @param string|null $type
     * @param float|null  $score
     * @param array       $attributes
     */
    public function __construct(array $attributes, $result = null, $index = null, $type = null, $score = null)
    {
        foreach ($attributes as $key => $attribute) {
            if (is_array($attribute)) {
                $attributes[$key] = new Result($attribute);
            }
        }

        $this->result = $result;
        $this->index = $index;
        $this->type = $type;
        $this->score = $score;
        $this->attributes = $attributes;
    }

    /**
     * Returns the result.
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Return the index that was queried.
     *
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Return the type of result.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Return the score matched for the result.
     *
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Get an attribute.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
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