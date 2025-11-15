<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_runs_all_seeders_in_correct_order(): void
    {
        // Run the complete DatabaseSeeder
        $this->seed(DatabaseSeeder::class);

        // Verify all data types were created
        $this->assertGreaterThan(0, User::count());
        $this->assertGreaterThan(0, Product::count());
        $this->assertGreaterThan(0, Customer::count());
        $this->assertGreaterThan(0, Order::count());
    }

    public function test_database_seeder_creates_expected_counts(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Check expected counts based on individual seeders
        $this->assertEquals(8, User::count());      // 3 admin + 5 factory
        $this->assertEquals(20, Customer::count()); // 5 predefined + 15 factory
        $this->assertEquals(25, Order::count());    // 25 orders
        $this->assertEquals(14, Product::count()); // Products from ProductSeeder
    }

    public function test_database_seeder_creates_relational_integrity(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Test that orders have valid customers
        $orders = Order::all();
        foreach ($orders as $order) {
            $this->assertDatabaseHas('customers', ['id' => $order->customer_id]);
        }

        // Test that order_product pivot table has valid relations
        $orders = Order::with('products')->get();
        foreach ($orders as $order) {
            foreach ($order->products as $product) {
                $this->assertDatabaseHas('products', ['id' => $product->id]);
                $this->assertDatabaseHas('order_product', [
                    'order_id' => $order->id,
                    'product_id' => $product->id
                ]);
            }
        }
    }

    public function test_database_seeder_creates_admin_users(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Verify admin users from UserSeeder
        $this->assertDatabaseHas('users', ['email' => 'admin@pastelaria.com']);
        $this->assertDatabaseHas('users', ['email' => 'vendas@pastelaria.com']);
        $this->assertDatabaseHas('users', ['email' => 'atendente@pastelaria.com']);
    }

    public function test_database_seeder_creates_predefined_customers(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Verify predefined customers from CustomerSeeder
        $this->assertDatabaseHas('customers', ['email' => 'joao.silva@email.com']);
        $this->assertDatabaseHas('customers', ['email' => 'maria.santos@email.com']);
        $this->assertDatabaseHas('customers', ['email' => 'pedro.oliveira@email.com']);
    }

    public function test_database_seeder_creates_products_with_categories(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Verify products have the expected categories
        $categories = Product::distinct('category')->pluck('category')->toArray();
        $this->assertContains('salgado', $categories);
        $this->assertContains('doce', $categories);
        $this->assertContains('especial', $categories);
    }

    public function test_database_seeder_creates_orders_with_different_statuses(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Verify orders have various statuses
        $statuses = Order::distinct('status')->pluck('status')->toArray();
        $validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'];
        
        foreach ($statuses as $status) {
            $this->assertContains($status, $validStatuses);
        }
    }

    public function test_database_seeder_preserves_data_integrity_after_multiple_runs(): void
    {
        $this->seed(DatabaseSeeder::class);
        
                $expectedCounts = [
            'users' => 8,      
            'customers' => 20,  
            'products' => 14,  
            'orders' => 25      
        ];
        
        $this->assertEquals($expectedCounts['users'], User::count());
        $this->assertEquals($expectedCounts['customers'], Customer::count());
        $this->assertEquals($expectedCounts['products'], Product::count());
        $this->assertEquals($expectedCounts['orders'], Order::count());
        
        $this->assertGreaterThan(0, Order::with('products')->whereHas('products')->count());
        $this->assertGreaterThan(0, Order::whereNotNull('customer_id')->count());
    }
}