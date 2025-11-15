<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find products by name pattern
     */
    public function findByName(string $name): Collection;

    /**
     * Find products by price range
     */
    public function findByPriceRange(float $minPrice, float $maxPrice): Collection;

    /**
     * Get products with their orders
     */
    public function getWithOrders(): Collection;

    /**
     * Get most ordered products
     */
    public function getMostOrdered(int $limit = 10): Collection;
}