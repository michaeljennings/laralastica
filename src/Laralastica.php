<?php

namespace Michaeljennings\Laralastica;

use Illuminate\Http\Request;

class Laralastica
{
    /**
     * The client manager.
     *
     * @var ClientManager
     */
    private $manager;

    /**
     * The laralastica config.
     *
     * @var array
     */
    protected $config;

    /**
     * The current request.
     *
     * @var Request
     */
    protected $request;

    public function __construct(ClientManager $manager, array $config, Request $request)
    {
        $this->manager = $manager;
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * Search for the results using the provided callback.
     *
     * @param string|array $types
     * @param callable     $query
     * @return ResultCollection
     */
    public function search($types, callable $query)
    {
        $builder = $this->newBuilder();

        $query($builder);

        return $builder->get($types);
    }

    /**
     * Search and paginate the results.
     *
     * @param string|array $types
     * @param callable     $query
     * @param int          $perPage
     * @return ResultCollection
     */
    public function paginate($types, callable $query, $perPage)
    {
        $page = $this->request->has('page') ? $this->request->get('page') : 1;
        $offset = $perPage * ($page - 1);
        $builder = $this->newBuilder();

        $query($builder);

        return $this->results = $builder->paginate($types, $page, $perPage, $offset);
    }

    /**
     * Create a new query builder.
     *
     * @return Builder
     */
    protected function newBuilder()
    {
        return new Builder($this->manager);
    }
}