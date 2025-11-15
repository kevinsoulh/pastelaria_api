<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function findByCustomer(Customer $customer): Collection
    {
        return $this->model->where('customer_id', $customer->id)
            ->with(['products'])
            ->latest('order_date')
            ->get();
    }

    public function findByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
            ->with(['customer', 'products'])
            ->latest('order_date')
            ->get();
    }

    public function findByDateRange(string $dateFrom, string $dateTo): Collection
    {
        return $this->model->whereBetween('order_date', [$dateFrom, $dateTo])
            ->with(['customer', 'products'])
            ->latest('order_date')
            ->get();
    }

    public function getWithRelations(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with([
            'customer:id,name,email,phone',  // Select specific columns
            'products:id,name,price,category' // Select specific columns
        ]);
        
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['date_from'])) {
            $query->whereDate('order_date', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->whereDate('order_date', '<=', $filters['date_to']);
        }
        
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy('order_date', $sortOrder === 'desc' ? 'desc' : 'asc');
        
        return $query->paginate(15);
    }

    public function createWithProducts(Customer $customer, array $products, array $data = []): Order
    {
        return DB::transaction(function () use ($customer, $products, $data) {
            // Create the order
            $order = $this->create(array_merge([
                'customer_id' => $customer->id,
                'status' => 'pending',
                'total_amount' => 0,
                'order_date' => now(),
            ], $data));
            
            // Attach products with quantities and prices
            $totalAmount = 0;
            foreach ($products as $productData) {
                $product = Product::findOrFail($productData['product_id']);
                $quantity = $productData['quantity'];
                $unitPrice = $product->price;
                
                $order->products()->attach($product->id, [
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ]);
                
                $totalAmount += $quantity * $unitPrice;
            }
            
            // Update total amount
            $this->update($order, ['total_amount' => $totalAmount]);
            
            return $order->load(['customer', 'products']);
        });
    }

    public function getStatistics(): array
    {
        $totalOrders = $this->model->count();
        $totalRevenue = $this->model->sum('total_amount');
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        
        $statusCounts = $this->model->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
            
        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'average_order_value' => round($averageOrderValue, 2),
            'status_distribution' => $statusCounts,
        ];
    }
}