<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'price',
                            'category',
                            'description',
                            'is_available'
                        ]
                    ],
                    'meta' => [
                        'current_page',
                        'total',
                        'per_page'
                    ]
                ])
                ->assertJsonPath('success', true);
    }

    public function test_can_create_product(): void
    {
        $productData = [
            'name' => 'Pastel de Queijo',
            'price' => 5.50,
            'category' => 'salgado',
            'description' => 'Delicioso pastel de queijo',
            'photo' => 'pastels/pastel_queijo.jpg'
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(201)
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.name', 'Pastel de Queijo')
                ->assertJsonPath('data.price', '5.50')
                ->assertJsonPath('data.category', 'salgado');

        $this->assertDatabaseHas('products', $productData);
    }

    public function test_create_product_validation_fails(): void
    {
        $response = $this->postJson('/api/v1/products', []);

        $response->assertStatus(422)
                ->assertJsonPath('success', false)
                ->assertJsonValidationErrors(['name', 'price', 'category', 'photo']);
    }

    public function test_can_show_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.id', $product->id)
                ->assertJsonPath('data.name', $product->name);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Original Name',
            'price' => 10.00
        ]);
        
        $updateData = [
            'name' => 'Updated Name',
            'price' => 15.00
        ];

        $response = $this->putJson("/api/v1/products/{$product->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.name', 'Updated Name')
                ->assertJsonPath('data.price', '15.00');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'price' => 15.00
        ]);
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('message', 'Product deleted successfully');

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_can_filter_products_by_category(): void
    {
        Product::factory()->create(['category' => 'salgado']);
        Product::factory()->create(['category' => 'doce']);
        Product::factory()->create(['category' => 'salgado']);

        $response = $this->getJson('/api/v1/products?category=salgado');

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_available_products(): void
    {
        Product::factory()->create(['is_available' => true]);
        Product::factory()->create(['is_available' => false]);
        Product::factory()->create(['is_available' => true]);

        $response = $this->getJson('/api/v1/products?available=1');

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonCount(2, 'data');
    }

    public function test_shows_product_with_orders(): void
    {
        $product = Product::factory()->create();
        $order = Order::factory()->create();
        $order->products()->attach($product, [
            'quantity' => 2,
            'unit_price' => $product->price
        ]);

        $response = $this->getJson("/api/v1/products/{$product->id}?include=orders");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'orders'
                    ]
                ])
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.id', $product->id);
    }
}
