<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find orders by customer
     */
    public function findByCustomer(Customer $customer): Collection;

    /**
     * Find orders by status
     */
    public function findByStatus(string $status): Collection;

    /**
     * Find orders by date range
     */
    public function findByDateRange(string $dateFrom, string $dateTo): Collection;

    /**
     * Get orders with customer and products
     */
    public function getWithRelations(array $filters = []): LengthAwarePaginator;

    /**
     * Create order with products
     */
    public function createWithProducts(Customer $customer, array $products, array $data = []): Order;

    /**
     * Get order statistics
     */
    public function getStatistics(): array;
}