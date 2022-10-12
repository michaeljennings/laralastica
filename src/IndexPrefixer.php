<?php

namespace Michaeljennings\Laralastica;

use Illuminate\Support\Str;

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
        if ( ! Str::startsWith($index, config('laralastica.index_prefix'))) {
            return config('laralastica.index_prefix') . $index;
        }

        return $index;
    }
}
