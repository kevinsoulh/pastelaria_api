<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected ProductRepository $productRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productRepository = app(ProductRepository::class);
    }

    public function test_can_create_product_through_repository(): void
    {
        $productData = [
            'name' => 'Pastel de Carne',
            'description' => 'Delicioso pastel de carne moída temperada',
            'price' => 5.50,
            'category' => 'salgado',
            'is_available' => true
        ];

        $product = $this->productRepository->create($productData);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals($productData['name'], $product->name);
        $this->assertEquals($productData['price'], $product->price);
        $this->assertEquals($productData['category'], $product->category);
        $this->assertTrue($product->is_available);
        
        // Assert in database
        $this->assertDatabaseHas('products', [
            'name' => $productData['name'],
            'price' => $productData['price'],
            'category' => $productData['category']
        ]);
    }

    public function test_can_find_products_by_name(): void
    {
        // Create test products
        Product::factory()->create(['name' => 'Pastel de Queijo']);
        Product::factory()->create(['name' => 'Pastel de Carne']);
        Product::factory()->create(['name' => 'Coxinha']);

        $results = $this->productRepository->findByName('Pastel');
        
        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn($product) => str_contains($product->name, 'Pastel')));
    }

    public function test_can_find_products_by_price_range(): void
    {
        // Create test products with different prices
        Product::factory()->create(['price' => 3.00]);
        Product::factory()->create(['price' => 5.50]);
        Product::factory()->create(['price' => 8.00]);
        Product::factory()->create(['price' => 12.00]);

        $results = $this->productRepository->findByPriceRange(4.00, 9.00);
        
        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn($product) => $product->price >= 4.00 && $product->price <= 9.00));
    }

    public function test_can_get_available_products(): void
    {
        Product::factory()->create(['is_available' => true]);
        Product::factory()->create(['is_available' => true]);
        Product::factory()->create(['is_available' => false]);

        $results = $this->productRepository->getAvailable();
        
        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn($product) => $product->is_available === true));
    }

    public function test_can_update_product_through_repository(): void
    {
        $product = Product::factory()->create([
            'name' => 'Pastel Original',
            'price' => 4.00
        ]);

        $updateData = [
            'name' => 'Pastel Atualizado',
            'price' => 6.00
        ];

        $updatedProduct = $this->productRepository->update($product, $updateData);

        $this->assertEquals($updateData['name'], $updatedProduct->name);
        $this->assertEquals($updateData['price'], $updatedProduct->price);
        
        // Assert in database
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $updateData['name'],
            'price' => $updateData['price']
        ]);
    }

    public function test_can_delete_product_through_repository(): void
    {
        $product = Product::factory()->create();
        
        $this->productRepository->delete($product);
        
        // With soft delete, the record still exists but with deleted_at timestamp
        $this->assertDatabaseHas('products', [
            'id' => $product->id
        ]);
        $this->assertSoftDeleted('products', [
            'id' => $product->id
        ]);
    }

    public function test_repository_implements_correct_interface(): void
    {
        $this->assertInstanceOf(ProductRepositoryInterface::class, $this->productRepository);
    }

    public function test_can_paginate_products(): void
    {
        Product::factory()->count(25)->create();

        $paginator = $this->productRepository->paginate(10);
        
        $this->assertCount(10, $paginator->items());
        $this->assertEquals(25, $paginator->total());
        $this->assertEquals(3, $paginator->lastPage());
    }

    public function test_can_find_product_with_orders_relationship()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);
        
        $product->orders()->attach($order->id, ['quantity' => 1, 'unit_price' => 10.00]);

        $result = $this->productRepository->find($product->id, ['orders']);

        $this->assertNotNull($result);
        $this->assertTrue($result->relationLoaded('orders'));
    }

    public function test_can_get_products_with_orders()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);
        
        $product->orders()->attach($order->id, ['quantity' => 1, 'unit_price' => 10.00]);

        $results = $this->productRepository->getWithOrders();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $results);
        $this->assertTrue($results->first()->relationLoaded('orders'));
        $this->assertTrue($results->first()->orders->first()->relationLoaded('customer'));
    }

    public function test_can_get_most_ordered_products()
    {
        $customer = Customer::factory()->create();
        $product1 = Product::factory()->create(['name' => 'Popular Product']);
        $product2 = Product::factory()->create(['name' => 'Less Popular Product']);
        
        // Create multiple orders for product1
        $order1 = Order::factory()->create(['customer_id' => $customer->id]);
        $order2 = Order::factory()->create(['customer_id' => $customer->id]);
        $order3 = Order::factory()->create(['customer_id' => $customer->id]);
        
        $product1->orders()->attach([$order1->id, $order2->id, $order3->id], ['quantity' => 1, 'unit_price' => 10.00]);
        
        // Create single order for product2
        $order4 = Order::factory()->create(['customer_id' => $customer->id]);
        $product2->orders()->attach($order4->id, ['quantity' => 1, 'unit_price' => 10.00]);

        $results = $this->productRepository->getMostOrdered(5);

        $this->assertCount(2, $results);
        $this->assertEquals('Popular Product', $results->first()->name);
        $this->assertEquals(3, $results->first()->orders_count);
    }
}