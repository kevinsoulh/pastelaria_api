<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_be_created_with_valid_data(): void
    {
        $productData = [
            'name' => 'Pastel de Carne',
            'price' => 8.50,
            'photo' => 'pastel_carne.jpg',
        ];

        $product = Product::create($productData);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Pastel de Carne', $product->name);
        $this->assertEquals(8.50, $product->price);
        $this->assertEquals('pastel_carne.jpg', $product->photo);
        $this->assertDatabaseHas('products', $productData);
    }

    public function test_product_price_is_cast_to_decimal(): void
    {
        $product = Product::factory()->create([
            'price' => 15.75
        ]);

        $this->assertIsString($product->price);
        $this->assertEquals('15.75', $product->price);
    }

    public function test_product_has_orders_relationship(): void
    {
        $product = Product::factory()->create();
        
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $product->orders()
        );
    }

    public function test_product_can_belong_to_multiple_orders(): void
    {
        $product = Product::factory()->create();
        $orders = Order::factory()->count(2)->create();

        foreach ($orders as $order) {
            $order->products()->attach($product->id, [
                'quantity' => 2,
                'unit_price' => $product->price
            ]);
        }

        $this->assertCount(2, $product->orders);
    }

    public function test_product_uses_soft_deletes(): void
    {
        $product = Product::factory()->create();
        $product->delete();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertNotNull($product->fresh()->deleted_at);
    }

    public function test_product_fillable_attributes(): void
    {
        $product = new Product();
        
        $expectedFillable = [
            'name',
            'description',
            'price',
            'category',
            'photo',
            'is_available'
        ];

        $this->assertEquals($expectedFillable, $product->getFillable());
    }

    public function test_product_photo_can_be_null(): void
    {
        $product = Product::factory()->create([
            'photo' => null
        ]);

        $this->assertNull($product->photo);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'photo' => null
        ]);
    }

    public function test_product_orders_relationship_includes_pivot_data()
    {
        $product = Product::factory()->create();
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $product->orders()->attach($order->id, [
            'quantity' => 3,
            'unit_price' => 25.00
        ]);

        $orderRelation = $product->orders->first();
        $this->assertEquals(3, $orderRelation->pivot->quantity);
        $this->assertEquals(25.00, $orderRelation->pivot->unit_price);
    }

    public function test_product_orders_with_customers_relationship()
    {
        $product = Product::factory()->create();
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);
        $product->orders()->attach($order->id, ['quantity' => 1, 'unit_price' => 10.00]);

        $ordersWithCustomers = $product->ordersWithCustomers;

        $this->assertCount(1, $ordersWithCustomers);
        $this->assertTrue($ordersWithCustomers->first()->relationLoaded('customer'));
    }

    public function test_product_available_scope()
    {
        Product::factory()->create(['is_available' => true, 'name' => 'Available Product']);
        Product::factory()->create(['is_available' => false, 'name' => 'Unavailable Product']);

        $availableProducts = Product::available()->get();

        $this->assertCount(1, $availableProducts);
        $this->assertEquals('Available Product', $availableProducts->first()->name);
    }
}
