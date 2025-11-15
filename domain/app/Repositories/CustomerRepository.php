<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): Collection
    {
        return $this->model->where('email', 'like', "%{$email}%")->get();
    }

    public function findByName(string $name): Collection
    {
        return $this->model->where('name', 'like', "%{$name}%")->get();
    }

    public function getWithOrders(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with([
            'orders:id,customer_id,total_amount,status,order_date',
            'orders.products:id,name,price'
        ]);
        
        if (isset($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }
        
        if (isset($filters['email'])) {
            $query->where('email', 'like', "%{$filters['email']}%");
        }
        
        return $query->latest()->paginate(15);
    }

    public function isEmailUnique(string $email, ?int $excludeId = null): bool
    {
        $query = $this->model->where('email', $email);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }
}