<?php

if ( ! function_exists('laralastica')) {

    /**
     * Helper method to resolve the laralastica class.
     *
     * @param string|array|null          $types
     * @param callable|null $callback
     * @return mixed
     */
    function laralastica($types = null, callable $callback = null)
    {
        $laralastica = app('laralastica');

        if ($types) {
            return $laralastica->search($types, $callback);
        }

        return $laralastica;
    }

}