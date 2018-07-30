<?php

namespace Michaeljennings\Laralastica;

class IndexPrefixer
{
    /**
     * Add the index prefix to the index.
     *
     * @param string $index
     * @return string
     */
    public function prefix(string $index)
    {
        if ( ! starts_with($index, config('laralastica.index_prefix'))) {
            return config('laralastica.index_prefix') . $index;
        }

        return $index;
    }
}