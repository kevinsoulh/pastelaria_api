<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    /**
     * Get all records with optional relationships
     */
    public function all(array $with = []): Collection;

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15, array $with = []): LengthAwarePaginator;

    /**
     * Find record by ID
     */
    public function find(int $id, array $with = []): ?Model;

    /**
     * Find record by ID or fail
     */
    public function findOrFail(int $id, array $with = []): Model;

    /**
     * Create new record
     */
    public function create(array $data): Model;

    /**
     * Update existing record
     */
    public function update(Model $model, array $data): Model;

    /**
     * Delete record
     */
    public function delete(Model $model): bool;

    /**
     * Find records by criteria
     */
    public function findBy(array $criteria, array $with = []): Collection;

    /**
     * Find single record by criteria
     */
    public function findOneBy(array $criteria, array $with = []): ?Model;
}