<?php

namespace App\Repositories;

use App\Models\Tenant;
use Illuminate\Database\QueryException;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * The model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Create a new repository instance.
     */
    public function __construct()
    {
        $this->model = app($this->getModelClass());
    }

    /**
     * Get the model class name.
     */
    abstract protected function getModelClass(): string;

    /**
     * Get all resources.
     *
     * @param  array  $columns  Specific columns to retrieve
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(array $columns = ['*'])
    {
        try {
            return $this->query()->select($columns)->get();
        } catch (QueryException $e) {
            app_log('Error fetching all resources', 'error', $e);
            throw $e;
        }
    }

    /**
     * Find a resource by ID.
     *
     * @param  int  $id
     * @param  array  $columns  Specific columns to retrieve
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find($id, array $columns = ['*'])
    {
        try {
            return $this->query()->select($columns)->find($id);
        } catch (QueryException $e) {
            app_log('Error finding resource by ID', 'error', $e);
            throw $e;
        }
    }

    /**
     * Find a resource by ID or throw an exception.
     *
     * @param  int  $id
     * @param  array  $columns  Specific columns to retrieve
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail($id, array $columns = ['*'])
    {
        try {
            return $this->query()->select($columns)->findOrFail($id);
        } catch (QueryException $e) {
            app_log('Error finding resource by ID or failing', 'error', $e);
            throw $e;
        }
    }

    /**
     * Create a new resource.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data)
    {
        try {
            $model = $this->model->newInstance($data);
            $model->save();

            return $model;
        } catch (QueryException $e) {
            app_log('Error creating resource', 'error', $e);
            throw $e;
        }
    }

    /**
     * Update a resource.
     *
     * @param  int  $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($id, array $data)
    {
        try {
            $model = $this->findOrFail($id);
            $model->fill($data);
            $model->save();

            return $model;
        } catch (QueryException $e) {
            app_log('Error updating resource', 'error', $e);
            throw $e;
        }
    }

    /**
     * Delete a resource.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete($id)
    {
        try {
            return $this->findOrFail($id)->delete();
        } catch (QueryException $e) {
            app_log('Error deleting resource', 'error', $e);
            throw $e;
        }
    }

    /**
     * Get a new query builder for the model.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        // The tenant scope is automatically applied via the trait's global scope
        return $this->model->newQuery();
    }

    /**
     * Get a specific number of records with pagination
     *
     * @param  int  $perPage  Number of items per page
     * @param  array  $columns  Specific columns to retrieve
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*'])
    {
        try {
            return $this->query()->select($columns)->paginate($perPage);
        } catch (QueryException $e) {
            app_log('Error paginating resources', 'error', $e);
            throw $e;
        }
    }

    /**
     * Find records by field and value
     *
     * @param  string  $field  Field to search by
     * @param  mixed  $value  Value to match
     * @param  array  $columns  Specific columns to retrieve
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByField(string $field, $value, array $columns = ['*'])
    {
        try {
            return $this->query()->select($columns)->where($field, $value)->get();
        } catch (QueryException $e) {
            app_log("Error finding resources by field '{$field}'", 'error', $e);
            throw $e;
        }
    }

    /**
     * Find the first record by field and value
     *
     * @param  string  $field  Field to search by
     * @param  mixed  $value  Value to match
     * @param  array  $columns  Specific columns to retrieve
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findOneByField(string $field, $value, array $columns = ['*'])
    {
        try {
            return $this->query()->select($columns)->where($field, $value)->first();
        } catch (QueryException $e) {
            app_log("Error finding one resource by field '{$field}'", 'error', $e);
            throw $e;
        }
    }

    /**
     * Get count of records with optional WHERE condition
     *
     * @param  string|null  $field  Field to search by
     * @param  mixed|null  $value  Value to match
     */
    public function count(?string $field = null, $value = null): int
    {
        try {
            $query = $this->query();

            if ($field !== null && $value !== null) {
                $query->where($field, $value);
            }

            return $query->count();
        } catch (QueryException $e) {
            app_log('Error counting resources', 'error', $e);
            throw $e;
        }
    }

    /**
     * Ensures that tenant context is set before executing operations.
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function ensureTenantContext()
    {
        if (! Tenant::checkCurrent() && $this->requiresTenantContext()) {
            throw new \Exception('Tenant context required for this operation');
        }
    }

    /**
     * Determines if this repository requires tenant context.
     */
    protected function requiresTenantContext(): bool
    {
        // Check if model uses BelongsToTenant trait
        $reflection = new \ReflectionClass($this->getModelClass());
        foreach ($reflection->getTraitNames() as $trait) {
            if (strpos($trait, 'BelongsToTenant') !== false) {
                return true;
            }
        }

        return false;
    }
}
