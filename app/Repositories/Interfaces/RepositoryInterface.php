<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Query\Builder;

interface RepositoryInterface
{
    /**
     * Get all
     *
     * @return mixed
     */
    public function all();

    /**
     * Get one
     *
     * @param  $id
     * @return mixed
     */
    public function find($id);

    /**
     * Get one
     *
     * @return mixed
     */
    public function first();

    /**
     * Create
     *
     * @param  array $attributes
     * @return mixed
     */
    public function create($attributes = []);

    /**
     * Update
     *
     * @param  $id
     * @param  array $attributes
     * @return mixed
     */
    public function update($id, $attributes = []);

    /**
     * Delete
     *
     * @param  $id
     * @return mixed
     */
    public function delete($id);

    /**
     * Show
     *
     * @param  $id
     * @return mixed
     */
    public function show($id);

    /**
     * Get query
     *
     * @return Builder
     */
    public function getQuery();

    /**
     * Clear query
     *
     * @return Builder
     */
    public function clearQuery();

    /**
     * File all
     *
     * @param  array $filter
     * @param  bool  $toArray
     * @return mixed
     */
    public function findBy(array $filter, bool $toArray = true);

    /**
     * Find one
     *
     * @param  array $filter
     * @param  bool  $toArray
     * @return mixed
     */
    public function findOneBy(array $filter, bool $toArray = true);

    /**
     * @param  array $attributes
     * @param  array $params
     * @return void
     */
    public function updateWhere(
        array $attributes = [],
        array $params = []
    ): void;
}
