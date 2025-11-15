<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;

Route::prefix('v1')->group(function () {
    
    Route::apiResource('customers', CustomerController::class);
    
    Route::apiResource('products', ProductController::class);
    
    Route::apiResource('orders', OrderController::class);
    
    Route::get('orders-statistics', [OrderController::class, 'statistics'])
        ->name('orders.statistics');
        
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])
        ->name('orders.update-status');
        
    Route::get('customers/{customer}/orders', function ($customer) {
        $customerModel = \App\Models\Customer::findOrFail($customer);
        $orders = $customerModel->orders()->with('products')->get();
        
        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    })->name('customers.orders');
    
    Route::get('products/{product}/orders', function ($product) {
        $productModel = \App\Models\Product::findOrFail($product);
        $orders = $productModel->orders()->with('customer')->get();
        
        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    })->name('products.orders');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::get('/endpoints', function () {
    return response()->json([
        'success' => true,
        'message' => 'Pastelaria API v1.0',
        'endpoints' => [
            'customers' => [
                'GET /api/v1/customers' => 'List all customers (paginated)',
                'POST /api/v1/customers' => 'Create new customer',
                'GET /api/v1/customers/{id}' => 'Get customer by ID',
                'PUT /api/v1/customers/{id}' => 'Update customer',
                'DELETE /api/v1/customers/{id}' => 'Delete customer',
                'GET /api/v1/customers/{id}/orders' => 'Get customer orders',
            ],
            'products' => [
                'GET /api/v1/products' => 'List all products (paginated)',
                'POST /api/v1/products' => 'Create new product',
                'GET /api/v1/products/{id}' => 'Get product by ID',
                'PUT /api/v1/products/{id}' => 'Update product',
                'DELETE /api/v1/products/{id}' => 'Delete product',
                'GET /api/v1/products/{id}/orders' => 'Get product orders',
            ],
            'orders' => [
                'GET /api/v1/orders' => 'List all orders (paginated)',
                'POST /api/v1/orders' => 'Create new order',
                'GET /api/v1/orders/{id}' => 'Get order by ID',
                'PUT /api/v1/orders/{id}' => 'Update order',
                'DELETE /api/v1/orders/{id}' => 'Cancel order',
                'PATCH /api/v1/orders/{id}/status' => 'Update order status',
            ]
        ],
        'query_parameters' => [
            'customers' => 'name, email',
            'products' => 'name, min_price, max_price, sort_by, sort_order',
            'orders' => 'customer_id, status, date_from, date_to, sort_order'
        ]
    ]);
});
