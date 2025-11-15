<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Database\Seeders\OrderSeeder;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed([
            CustomerSeeder::class,
            ProductSeeder::class
        ]);
    }

    public function test_order_seeder_creates_orders(): void
    {
        $this->seed(OrderSeeder::class);

        $this->assertEquals(25, Order::count());
    }

    public function test_order_seeder_requires_customers_and_products(): void
    {
        Order::truncate();
        Customer::truncate(); 
        Product::truncate();
        
        $this->seed(OrderSeeder::class);

        $this->assertEquals(0, Order::count());
    }

    public function test_order_seeder_creates_orders_with_valid_data(): void
    {
        $this->seed(OrderSeeder::class);

        $orders = Order::all();
        
        foreach ($orders as $order) {
            $this->assertNotNull($order->customer_id);
            $this->assertGreaterThan(0, $order->total_amount);
            $this->assertNotNull($order->status);
            $this->assertNotNull($order->order_date);
            
            $this->assertDatabaseHas('customers', ['id' => $order->customer_id]);
        }
    }

    public function test_order_seeder_creates_orders_with_valid_statuses(): void
    {
        $this->seed(OrderSeeder::class);

        $validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'];
        $orders = Order::all();
        
        foreach ($orders as $order) {
            $this->assertContains($order->status, $validStatuses);
        }
    }

    public function test_order_seeder_attaches_products_to_orders(): void
    {
        $this->seed(OrderSeeder::class);

        $orders = Order::with('products')->get();
        
        foreach ($orders as $order) {
            $this->assertGreaterThan(0, $order->products->count());
            $this->assertLessThanOrEqual(4, $order->products->count()); 
            
            foreach ($order->products as $product) {
                $this->assertGreaterThan(0, $product->pivot->quantity);
                $this->assertGreaterThan(0, $product->pivot->unit_price);
                $this->assertLessThanOrEqual(3, $product->pivot->quantity); 
            }
        }
    }

    public function test_order_seeder_calculates_correct_total_amounts(): void
    {
        $this->seed(OrderSeeder::class);

        $orders = Order::with('products')->get();
        
        foreach ($orders as $order) {
            $calculatedTotal = 0;
            
            foreach ($order->products as $product) {
                $calculatedTotal += $product->pivot->quantity * $product->pivot->unit_price;
            }
            
            $this->assertEquals($calculatedTotal, (float) $order->total_amount);
        }
    }

    public function test_order_seeder_creates_orders_with_different_dates(): void
    {
        $this->seed(OrderSeeder::class);

        $orders = Order::all();
        $orderDates = $orders->pluck('order_date')->unique();
        
        $this->assertGreaterThan(1, $orderDates->count());
        
        foreach ($orders as $order) {
            $daysDiff = now()->diffInDays($order->order_date);
            $this->assertLessThanOrEqual(30, $daysDiff);
        }
    }

    public function test_order_seeder_creates_some_orders_with_notes(): void
    {
        $this->seed(OrderSeeder::class);

        $ordersWithNotes = Order::whereNotNull('notes')->count();
        $ordersWithoutNotes = Order::whereNull('notes')->count();
        
        $this->assertGreaterThan(0, $ordersWithNotes);
        $this->assertGreaterThan(0, $ordersWithoutNotes);
        $this->assertEquals(25, $ordersWithNotes + $ordersWithoutNotes);
    }
}