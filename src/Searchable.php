<?php namespace Michaeljennings\Laralastica;

use Closure;

trait Searchable {

    /**
     * @param callable $query
     * @param string $key
     */
    public function search(Closure $query, $key = 'id')
    {
        if ( ! isset($this->type)) {
            $this->type = $this->table;
        }

        $results = $this->laralastica->search($this->type, $query);

        dd($results);
    }

}