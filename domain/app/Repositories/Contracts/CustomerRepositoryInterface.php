<?php

namespace App\Repositories\Contracts;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find customers by email pattern
     */
    public function findByEmail(string $email): Collection;

    /**
     * Find customers by name pattern
     */
    public function findByName(string $name): Collection;

    /**
     * Get customers with their orders
     */
    public function getWithOrders(array $filters = []): LengthAwarePaginator;

    /**
     * Check if email is unique (excluding given customer)
     */
    public function isEmailUnique(string $email, ?int $excludeId = null): bool;
}