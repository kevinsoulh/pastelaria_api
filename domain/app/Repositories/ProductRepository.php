<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function findByName(string $name): Collection
    {
        return $this->model->where('name', 'like', "%{$name}%")->get();
    }

    public function findByPriceRange(float $minPrice, float $maxPrice): Collection
    {
        return $this->model->whereBetween('price', [$minPrice, $maxPrice])->get();
    }

    public function getWithOrders(): Collection
    {
        return $this->model->with([
            'orders:id,total_amount,status,order_date,customer_id',
            'orders.customer:id,name,email'
        ])->get();
    }

    public function getMostOrdered(int $limit = 10): Collection
    {
        return $this->model->withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getAvailable(): Collection
    {
        return $this->model->where('is_available', true)->get();
    }

    /**
     * Get most ordered products with optimized relationships
     */
    public function getMostOrderedOptimized(int $limit = 10): Collection
    {
        return $this->model
            ->select('products.*')
            ->withCount('orders')
            ->with('orders:id,total_amount,status')
            ->orderBy('orders_count', 'desc')
            ->limit($limit)
            ->get();
    }
}