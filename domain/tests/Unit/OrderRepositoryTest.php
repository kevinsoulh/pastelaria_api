<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Repositories\OrderRepository;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Carbon\Carbon;

class OrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected OrderRepository $orderRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderRepository = app(OrderRepository::class);
    }

    public function test_can_create_order_with_products(): void
    {
        $customer = Customer::factory()->create();
        $products = [
            ['product_id' => Product::factory()->create(['price' => 5.50])->id, 'quantity' => 2],
            ['product_id' => Product::factory()->create(['price' => 3.00])->id, 'quantity' => 1]
        ];

        $order = $this->orderRepository->createWithProducts($customer, $products, ['notes' => 'Teste']);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($customer->id, $order->customer_id);
        $this->assertEquals(14.00, $order->total_amount); // (5.50 * 2) + (3.00 * 1)
        $this->assertCount(2, $order->products);
        
        // Assert in database
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'customer_id' => $customer->id,
            'total_amount' => 14.00
        ]);
    }

    public function test_can_find_orders_by_customer(): void
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();
        
        Order::factory()->count(3)->create(['customer_id' => $customer1->id]);
        Order::factory()->count(2)->create(['customer_id' => $customer2->id]);

        $results = $this->orderRepository->findByCustomer($customer1);
        
        $this->assertCount(3, $results);
        $this->assertTrue($results->every(fn($order) => $order->customer_id === $customer1->id));
    }

    public function test_can_find_orders_by_status(): void
    {
        Order::factory()->count(2)->create(['status' => 'pending']);
        Order::factory()->count(3)->create(['status' => 'delivered']);
        Order::factory()->count(1)->create(['status' => 'cancelled']);

        $pendingOrders = $this->orderRepository->findByStatus('pending');
        $deliveredOrders = $this->orderRepository->findByStatus('delivered');
        
        $this->assertCount(2, $pendingOrders);
        $this->assertCount(3, $deliveredOrders);
        $this->assertTrue($pendingOrders->every(fn($order) => $order->status === 'pending'));
        $this->assertTrue($deliveredOrders->every(fn($order) => $order->status === 'delivered'));
    }

    public function test_can_find_orders_by_date_range(): void
    {
        // Create orders with specific dates 
        Order::factory()->create(['order_date' => '2025-01-01 10:00:00']);
        Order::factory()->create(['order_date' => '2025-01-02 10:00:00']);
        Order::factory()->create(['order_date' => '2025-01-05 10:00:00']);

        $results = $this->orderRepository->findByDateRange('2025-01-01', '2025-01-03');
        
        // Should include orders from 01-01 and 01-02, but not 01-05
        $this->assertCount(2, $results);
    }

    public function test_can_get_order_statistics(): void
    {
        // Create orders with different statuses and amounts
        Order::factory()->create(['status' => 'delivered', 'total_amount' => 100.00]);
        Order::factory()->create(['status' => 'delivered', 'total_amount' => 150.00]);
        Order::factory()->create(['status' => 'pending', 'total_amount' => 75.00]);
        Order::factory()->create(['status' => 'cancelled', 'total_amount' => 50.00]);

        $stats = $this->orderRepository->getStatistics();
        
        $this->assertArrayHasKey('total_orders', $stats);
        $this->assertArrayHasKey('total_revenue', $stats);
        $this->assertArrayHasKey('average_order_value', $stats);
        $this->assertArrayHasKey('status_distribution', $stats);
        
        $this->assertEquals(4, $stats['total_orders']);
        $this->assertEquals(375.00, $stats['total_revenue']);
        $this->assertEquals(93.75, $stats['average_order_value']); // 375 / 4
        $this->assertEquals(2, $stats['status_distribution']['delivered']);
        $this->assertEquals(1, $stats['status_distribution']['pending']);
        $this->assertEquals(1, $stats['status_distribution']['cancelled']);
    }

    public function test_can_update_order_through_repository(): void
    {
        $order = Order::factory()->create([
            'status' => 'pending',
            'notes' => 'Original notes'
        ]);

        $updateData = [
            'status' => 'preparing',
            'notes' => 'Updated notes'
        ];

        $updatedOrder = $this->orderRepository->update($order, $updateData);

        $this->assertEquals($updateData['status'], $updatedOrder->status);
        $this->assertEquals($updateData['notes'], $updatedOrder->notes);
        
        // Assert in database
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => $updateData['status'],
            'notes' => $updateData['notes']
        ]);
    }

    public function test_can_delete_order_through_repository(): void
    {
        $order = Order::factory()->create();
        
        $this->orderRepository->delete($order);
        
        // With soft delete, the record still exists but with deleted_at timestamp
        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }

    public function test_repository_implements_correct_interface(): void
    {
        $this->assertInstanceOf(OrderRepositoryInterface::class, $this->orderRepository);
    }

    public function test_can_get_orders_with_relations(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);
        $order->products()->attach($product->id, ['quantity' => 2, 'unit_price' => $product->price]);

        $filters = [];
        $paginator = $this->orderRepository->getWithRelations($filters);
        
        $firstOrder = $paginator->items()[0];
        $this->assertTrue($firstOrder->relationLoaded('customer'));
        $this->assertTrue($firstOrder->relationLoaded('products'));
    }

    public function test_can_filter_orders_with_relations(): void
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();
        
        Order::factory()->create(['customer_id' => $customer1->id, 'status' => 'pending']);
        Order::factory()->create(['customer_id' => $customer2->id, 'status' => 'delivered']);

        $filters = ['customer_id' => $customer1->id];
        $paginator = $this->orderRepository->getWithRelations($filters);
        
        $this->assertCount(1, $paginator->items());
        $this->assertEquals($customer1->id, $paginator->items()[0]->customer_id);
    }
}