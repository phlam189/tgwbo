<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseRepository implements RepositoryInterface
{
    // Model property on class instances
    protected $model;

    /**
     * 
     *
     * @var Builder 
     */
    protected $query;

    // Constructor to bind model to repo
    public function __construct()
    {
        $this->setModel();
        $this->query = $this->model->newQuery();
    }

    // Get the associated model
    abstract public function getModel();

    // Set the associated model
    public function setModel()
    {
        $this->model = app()->make($this->getModel());
    }

    // Get all instances of model
    public function all()
    {
        return $this->model->all();
    }

    // create a new record in the database
    public function create($attributes = [])
    {
        return $this->model->create($attributes);
    }

    // update record in the database
    public function update($id, $attributes = [])
    {
        $record = $this->show($id);
        return $record->update($attributes);
    }

    // remove record from the database
    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    // show the record with the given id
    public function show($id)
    {
        return $this->model->findOrFail($id);
    }

    // find the record with the given id
    public function find($id)
    {
        return $this->model->find($id);
    }

    // find the first record
    public function first()
    {
        return $this->model->first();
    }

    // Eager load database relationships
    public function with($relations)
    {
        return $this->model->with($relations);
    }

    /**
     * Get query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function getQuery()
    {
        return $this->query->getQuery();
    }

    /**
     * Clear query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function clearQuery()
    {
        $this->query = $this->model->newQuery();
        return $this->query->getQuery();
    }

    /**
     * File all
     *
     * @param  array $filter
     * @param  bool  $toArray
     * @return mixed
     */
    public function findBy(array $filter, bool $toArray = true)
    {
        $builder = $this->model->newQuery();
        foreach ($filter as $key => $val) {
            $builder->where($key, $val);
        }
        $find = $builder->get();

        if (!$toArray) {
            return $find;
        }
        return $find ? $find->toArray() : null;
    }

    /**
     * Find one
     *
     * @param  array $filter
     * @param  bool  $toArray
     * @return mixed
     */
    public function findOneBy(array $filter, bool $toArray = true)
    {
        $builder = $this->model->newQuery();
        foreach ($filter as $key => $val) {
            $builder->where($key, $val);
        }
        $data = $builder->first();

        if (!$toArray) {
            return $data;
        }
        return $data ? $data->toArray() : [];
    }

    /**
     * @param  array $attributes
     * @param  array $params
     * @return void
     */
    public function updateWhere(
        array $attributes = [],
        array $params = []
    ): void {
        $this->model->where($attributes)->update($params);
    }
}
