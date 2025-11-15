<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $with = []): Collection
    {
        $query = $this->model->newQuery();
        
        if (!empty($with)) {
            $query->with($with);
        }
        
        return $query->get();
    }

    public function paginate(int $perPage = 15, array $with = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();
        
        if (!empty($with)) {
            $query->with($with);
        }
        
        return $query->latest()->paginate($perPage);
    }

    public function find(int $id, array $with = []): ?Model
    {
        $query = $this->model->newQuery();
        
        if (!empty($with)) {
            $query->with($with);
        }
        
        return $query->find($id);
    }

    public function findOrFail(int $id, array $with = []): Model
    {
        $query = $this->model->newQuery();
        
        if (!empty($with)) {
            $query->with($with);
        }
        
        return $query->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->update($data);
        return $model->fresh();
    }

    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    public function findBy(array $criteria, array $with = []): Collection
    {
        $query = $this->model->newQuery();
        
        if (!empty($with)) {
            $query->with($with);
        }
        
        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }
        
        return $query->get();
    }

    public function findOneBy(array $criteria, array $with = []): ?Model
    {
        return $this->findBy($criteria, $with)->first();
    }
}