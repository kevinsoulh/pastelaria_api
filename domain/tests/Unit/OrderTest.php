<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_can_be_created_with_valid_data(): void
    {
        $customer = Customer::factory()->create();
        
        $orderData = [
            'customer_id' => $customer->id,
            'total_amount' => 25.50,
            'status' => 'pending',
            'notes' => 'Extra molho',
            'order_date' => now(),
        ];

        $order = Order::create($orderData);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($customer->id, $order->customer_id);
        $this->assertEquals('25.50', $order->total_amount);
        $this->assertEquals('pending', $order->status);
        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'status' => 'pending'
        ]);
    }

    public function test_order_belongs_to_customer(): void
    {
        $order = Order::factory()->create();
        
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $order->customer()
        );
        
        $this->assertInstanceOf(Customer::class, $order->customer);
    }

    public function test_order_has_many_products(): void
    {
        $order = Order::factory()->create();
        
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $order->products()
        );
    }

    public function test_order_can_calculate_total(): void
    {
        $order = Order::factory()->create();
        $product1 = Product::factory()->create(['price' => 10.00]);
        $product2 = Product::factory()->create(['price' => 15.00]);

        $order->products()->attach([
            $product1->id => ['quantity' => 2, 'unit_price' => 10.00],
            $product2->id => ['quantity' => 1, 'unit_price' => 15.00],
        ]);

        $expectedTotal = (2 * 10.00) + (1 * 15.00); // 35.00
        $this->assertEquals($expectedTotal, $order->calculateTotal());
    }

    public function test_order_uses_soft_deletes(): void
    {
        $order = Order::factory()->create();
        $order->delete();

        $this->assertSoftDeleted('orders', ['id' => $order->id]);
        $this->assertNotNull($order->fresh()->deleted_at);
    }

    public function test_order_fillable_attributes(): void
    {
        $order = new Order();
        
        $expectedFillable = [
            'customer_id',
            'total_amount',
            'status',
            'notes',
            'order_date',
        ];

        $this->assertEquals($expectedFillable, $order->getFillable());
    }

    public function test_order_total_amount_is_cast_to_decimal(): void
    {
        $order = Order::factory()->create([
            'total_amount' => 15.75
        ]);

        $this->assertIsString($order->total_amount);
        $this->assertEquals('15.75', $order->total_amount);
    }

    public function test_order_date_is_cast_to_datetime(): void
    {
        $order = Order::factory()->create([
            'order_date' => '2024-01-15 10:30:00'
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $order->order_date);
    }

    public function test_order_can_be_cancelled_when_pending_or_confirmed(): void
    {
        $pendingOrder = Order::factory()->create(['status' => 'pending']);
        $confirmedOrder = Order::factory()->create(['status' => 'confirmed']);
        $preparingOrder = Order::factory()->create(['status' => 'preparing']);
        $readyOrder = Order::factory()->create(['status' => 'ready']);
        $deliveredOrder = Order::factory()->create(['status' => 'delivered']);

        $this->assertTrue($pendingOrder->canBeCancelled());
        $this->assertTrue($confirmedOrder->canBeCancelled());
        $this->assertFalse($preparingOrder->canBeCancelled());
        $this->assertFalse($readyOrder->canBeCancelled());
        $this->assertFalse($deliveredOrder->canBeCancelled());
    }

    public function test_order_default_status_is_pending(): void
    {
        $customer = Customer::factory()->create();
        
        $order = Order::create([
            'customer_id' => $customer->id,
            'total_amount' => 10.00,
            'order_date' => now(),
        ]);

        // Check if the default is applied by the database, not the model
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending'
        ]);
    }

    public function test_order_products_relationship_includes_pivot_data(): void
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create(['price' => 12.50]);

        $order->products()->attach($product->id, [
            'quantity' => 2,
            'unit_price' => 12.50
        ]);

        $orderProduct = $order->products->first();
        $this->assertEquals(2, $orderProduct->pivot->quantity);
        $this->assertEquals(12.50, $orderProduct->pivot->unit_price);
    }
}
