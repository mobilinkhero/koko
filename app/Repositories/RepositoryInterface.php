<?php

namespace App\Repositories;

interface RepositoryInterface
{
    /**
     * Get all resources.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all();

    /**
     * Find a resource by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find($id);

    /**
     * Find a resource by ID or throw an exception.
     *
     * @param  int  $id
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail($id);

    /**
     * Create a new resource.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data);

    /**
     * Update a resource.
     *
     * @param  int  $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($id, array $data);

    /**
     * Delete a resource.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete($id);

    /**
     * Get a new query builder for the model.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query();
}
/*
    * This interface defines the basic CRUD operations for a repository pattern.
    * It allows for easy implementation of different repositories for different models.
    */
