<?php

namespace Michaeljennings\Laralastica;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Michaeljennings\Laralastica\Contracts\Laralastica as LaralasticaContract;

class Laralastica implements LaralasticaContract
{
    /**
     * The client manager.
     *
     * @var ClientManager
     */
    protected $manager;

    /**
     * The current request.
     *
     * @var Request
     */
    protected $request;

    public function __construct(ClientManager $manager, Request $request)
    {
        $this->manager = $manager;
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
     * @return LengthAwarePaginator
     */
    public function paginate($types, callable $query, $perPage)
    {
        $page = $this->request->has('page') ? $this->request->get('page') : 1;
        $offset = $perPage * ($page - 1);
        $builder = $this->newBuilder();

        $query($builder);

        return $builder->paginate($types, $page, $perPage, $offset);
    }

    /**
     * Add a new document to the provided type.
     *
     * @param string     $type
     * @param string|int $id
     * @param array      $data
     * @return $this
     */
    public function add($type, $id, array $data)
    {
        $builder = $this->newBuilder();

        $builder->add($type, $id, $data);

        return $this;
    }

    /**
     * Add multiple documents to the elasticsearch type. The data array must be a
     * multidimensional array with the key as the desired id and the value as
     * the data to be added to the document.
     *
     * @param string $type
     * @param array  $data
     * @return $this
     */
    public function addMultiple($type, array $data)
    {
        $builder = $this->newBuilder();

        $builder->addMultiple($type, $data);

        return $this;
    }

    /**
     * Delete a document from the provided type.
     *
     * @param string     $type
     * @param string|int $id
     * @return $this
     */
    public function delete($type, $id)
    {
        $builder = $this->newBuilder();

        $builder->delete($type, $id);

        return $this;
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